# WorkWize MDM — Jamf Device Sync

Laravel 11 JSON API + a standalone React (Vite + TanStack Query) SPA that imports **assigned** devices from a Jamf MDM mock payload into MySQL and surfaces them in a small admin UI.

Built against the case study spec in [`ASSIGNMENT.md`](./ASSIGNMENT.md).

> Heads up - I know that some parts (Reverb, the action layer, Redis for cache + queue) are heavier than the task strictly needed. I wanted to build it closer to how I'd actually ship this in a real project rather than just tick the boxes. If some bits feel over the top - that's why.
>
> Maksym

```
┌──────────────────────────┐   HTTP /api/*    ┌────────────────────────────────┐
│ ui/  (Vite, React, RQ)   │  ──────────────► │ Laravel 11 (JSON API)          │
│ http://localhost:5173    │                  │ http://localhost:8080/api      │
└──────────────────────────┘                  │   ├─ AssetController            │
                                              │   ├─ EmployeeController         │
                                              │   └─ ImportController           │
                                              │                                 │
                                              │ queue: RunMdmImportJob          │
                                              │   └─ MdmImportOrchestrator      │
                                              │       └─ MdmProvider (Jamf,…)   │
                                              │                                 │
                                              │ MySQL 8                         │
                                              └────────────────────────────────┘
```

---

## Quick start

```bash
docker compose up --build
```

Seven containers come up:

| Service  | Port | Purpose                                                              |
|----------|------|----------------------------------------------------------------------|
| `ui`     | 5173 | Vite dev server (React SPA)                                          |
| `web`    | 8080 | nginx → Laravel JSON API                                             |
| `app`    | —    | php-fpm                                                              |
| `queue`  | —    | `php artisan queue:work redis` worker                                |
| `reverb` | 8081 | Laravel Reverb (WebSocket server, Pusher protocol) — live sync push  |
| `mysql`  | 3306 | MySQL 8 (assets, employees, imports)                                 |
| `redis`  | 6379 | Cache store + queue backend (predis client, no PECL ext)             |

Then open **http://localhost:5173** in your browser.

The Laravel entrypoint (`docker/app/entrypoint.sh`) runs `composer install`, generates `APP_KEY`, waits for MySQL, runs migrations, and seeds `storage/app/jamf/api-mock-response.json` from `.assignment/`. The `ui` container runs `npm install` on first boot.

> First boot is 1–3 minutes (image builds + composer + npm). Subsequent boots are seconds.

---

## What "Sync Now" does

1. Browser → `POST /api/sync`.
2. Controller creates an `imports` row (status `queued`) and dispatches `RunMdmImportJob`.
3. The queue worker:
   - resolves the configured MDM provider via `MdmProviderRegistry`
   - streams `MappingResult`s from the provider
   - upserts employees (by email) and assets (by serial code) inside one DB transaction
   - writes counters into `imports.summary` and flips status to `succeeded` (or `failed`)
4. The job broadcasts `ImportUpdated` on the public channel `import.{id}` at every state transition (running, succeeded, failed). The UI subscribes via Laravel Echo + Reverb the moment it gets an import id back from POST — no polling. When the terminal event arrives, the UI invalidates the asset/employee queries via TanStack Query.

Re-running sync is idempotent: existing rows are updated, deleted rows are recreated, attribute changes are reflected.

---

## API surface

| Verb   | Path                          | Purpose                                                                  |
|--------|-------------------------------|--------------------------------------------------------------------------|
| GET    | `/api`                        | Service banner + endpoint list                                           |
| GET    | `/api/assets`                 | List assets (with eager-loaded `employee`)                               |
| GET    | `/api/assets/{asset}`         | Asset detail (`specs` + full employee)                                   |
| DELETE | `/api/assets/{asset}`         | Delete local asset row (204)                                             |
| GET    | `/api/employees`              | List employees (with `assets_count`)                                     |
| DELETE | `/api/employees/{employee}`   | Delete employee — **409** when assets are still assigned                 |
| POST   | `/api/imports`                | Trigger an import. Returns `202` + the new `import` record              |
| GET    | `/api/imports/{import}`       | Fetch import status / summary (mostly for state recovery — live updates come via Reverb) |

CORS: `/api/*` allows `http://localhost:5173` by default (configurable via `CORS_ALLOWED_ORIGINS`).

---

## Adding a new MDM provider

The provider abstraction is the load-bearing piece of the design. Adding a vendor (e.g. Kandji) means:

1. `app/Mdm/Kandji/KandjiDeviceMapper.php` — implements `App\Mdm\Contracts\DeviceMapper`. Returns a `MappingResult` per raw payload (DTO or typed skip reason).
2. `app/Mdm/Kandji/KandjiProvider.php` — implements `App\Mdm\Contracts\MdmProvider`. Fetches the raw payload (HTTP, file, …) and yields mapping results.
3. One line in `config/mdm.php#providers`: `'kandji' => KandjiProvider::class`.

That's it. Laravel's container auto-resolves both classes through reflection because their constructor dependencies are class-typed (a `KandjiDeviceMapper`, maybe an HTTP client, etc.). No service-provider boilerplate needed.

**When you'd add a `KandjiServiceProvider`:** only if the constructor needs a non-class arg (a string from config, an array, a closure). Drop the class under `app/Mdm/Kandji/` and register it in `bootstrap/providers.php`.

`AppServiceProvider`, the registry, the orchestrator, the job, the controllers, and the UI never need to change.

```
app/Mdm
├── Contracts/{DeviceMapper,MdmProvider}.php   # interfaces
├── Dto/DeviceDto.php                          # canonical, vendor-agnostic
├── Mapping/{MappingResult,SkipReason}.php     # ok|skip + enum
├── Jamf/{JamfDeviceMapper,JamfProvider}.php
├── DeviceUpserter.php
├── MdmProviderRegistry.php
├── MdmImportOrchestrator.php
└── ImportSummary.php
```

---

## Testing

```bash
docker compose exec app php artisan test
# or, on host (PHP 8.3 + composer):
php artisan test
```

16 feature tests covering every rule in **MDM Sync Behaviour Rules**:

- `Tests\Feature\Mdm\SyncBehaviourTest` — 10 tests, one per rule. Uses an in-memory `JamfArrayProvider` (reuses the real `JamfDeviceMapper`) so the production mapping + sync logic is exercised without disk IO.
- `Tests\Feature\Http\SyncEndpointTest` — end-to-end import against the real bundled mock JSON (7 devices → 7 assets, 6 employees).
- `Tests\Feature\Http\EmployeeDeleteTest` — 409 with `error_code: employee_has_assets`, 204 on success.
- `Tests\Feature\Http\AssetDeleteTest` — delete works + list eager-loads employee.

UI type-check:

```bash
docker compose exec ui npm run typecheck
```

## Repo layout

```
app/
├── Http/Controllers/{Asset,Employee,Sync}Controller.php   # JSON API
├── Jobs/RunMdmImportJob.php
├── Mdm/                                                   # provider-agnostic sync core
├── Models/{Asset,Employee,Import}.php
└── Providers/AppServiceProvider.php
bootstrap/{app.php, providers.php}                          # api routing + MDM providers
config/{mdm.php, cors.php}
database/migrations/                                        # employees, assets, imports
docker/
├── app/{Dockerfile, entrypoint.sh}
└── nginx/default.conf
docker-compose.yml
routes/{api.php, web.php}                                   # web.php is a tiny banner
tests/
├── Feature/Mdm/SyncBehaviourTest.php                       # 10 behaviour rule tests
├── Feature/Http/{Asset,Employee,Sync}*.php
└── Support/{JamfArrayProvider, DeviceFactory}.php
ui/                                                         # standalone React SPA
├── Dockerfile
├── package.json, tsconfig.json, vite.config.ts
├── tailwind.config.js, postcss.config.js
├── index.html
└── src/
    ├── main.tsx, App.tsx
    ├── api/{client, types, assets, employees, sync}.ts
    ├── components/{AppLayout, PageHeader, Toast}.tsx
    ├── hooks/useImport.ts
    └── pages/{AssetsIndex, AssetDetail, EmployeesIndex}.tsx
```

---

## Original assignment

See [`ASSIGNMENT.md`](./ASSIGNMENT.md). The reference service used as a baseline lives at [`.assignment/RefJamfSyncService.php`](./.assignment/RefJamfSyncService.php); its mapping logic is ported (not extended) into `app/Mdm/Jamf/JamfDeviceMapper.php`.
