<?php

declare(strict_types=1);

// Import progress is broadcast on a public channel `import.{id}` — no auth
// closure needed since the channel is public. In production this would
// become a private channel gated on the authenticated user owning the import.
