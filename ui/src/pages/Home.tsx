import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
    ArrowRight,
    History,
    Laptop,
    Linkedin,
    SquareStack,
    Users,
} from 'lucide-react';
import { useCallback } from 'react';
import { Link } from 'react-router-dom';

import { asApiError } from '@/api/client';
import { getStats } from '@/api/stats';
import type { ImportStatus } from '@/api/types';
import ImportButton from '@/components/ImportButton';
import PageHeader from '@/components/PageHeader';
import { Skeleton } from '@/components/Skeleton';
import StatusBadge from '@/components/StatusBadge';
import { useToast } from '@/components/Toast';
import { useImport } from '@/hooks/useImport';

export default function Home() {
    const toast = useToast();
    const queryClient = useQueryClient();

    const stats = useQuery({ queryKey: ['stats'], queryFn: getStats });

    const invalidate = useCallback(() => {
        queryClient.invalidateQueries({ queryKey: ['stats'] });
        queryClient.invalidateQueries({ queryKey: ['assets'] });
        queryClient.invalidateQueries({ queryKey: ['employees'] });
    }, [queryClient]);

    const importRun = useImport(() => {
        toast.push('Import complete.', 'success');
        invalidate();
    });

    const counts = stats.data?.counts;
    const lastImport = stats.data?.last_import;

    return (
        <>
            <PageHeader
                title="Dashboard"
                subtitle="Live state of the local MDM store."
                actions={<ImportButton onClick={importRun.trigger} inFlight={importRun.inFlight} />}
            />

            {importRun.error && (
                <div className="mb-4 px-4 py-3 rounded-lg border bg-rose-50 border-rose-200 text-rose-800 text-sm">
                    {importRun.error}
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    <section className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <StatCard
                            label="Assets"
                            value={counts?.assets}
                            loading={stats.isLoading}
                            href="/assets"
                            icon={Laptop}
                        />
                        <StatCard
                            label="Employees"
                            value={counts?.employees}
                            loading={stats.isLoading}
                            href="/employees"
                            icon={Users}
                        />
                        <StatCard
                            label="Imports"
                            value={counts?.imports}
                            loading={stats.isLoading}
                            icon={History}
                        />
                    </section>

                    <section className="bg-white shadow-sm rounded-xl border border-slate-200 p-6">
                        <h2 className="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                            <History className="h-4 w-4 text-slate-400" />
                            Last import
                        </h2>
                        {stats.isLoading && (
                            <div className="space-y-2">
                                <Skeleton className="h-4 w-1/3" />
                                <Skeleton className="h-4 w-1/2" />
                                <Skeleton className="h-4 w-1/4" />
                            </div>
                        )}
                        {stats.isError && (
                            <div className="text-rose-600 text-sm">
                                {asApiError(stats.error).message}
                            </div>
                        )}
                        {stats.data && !lastImport && (
                            <div className="text-slate-500 text-sm">
                                No imports yet. Hit <span className="font-semibold">Run Import</span>{' '}
                                above to pull devices from Jamf.
                            </div>
                        )}
                        {lastImport && (
                            <dl className="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                <Row label="Provider" value={lastImport.provider} />
                                <Row
                                    label="Status"
                                    value={<StatusBadge status={lastImport.status as ImportStatus} />}
                                />
                                <Row
                                    label="Started"
                                    value={
                                        lastImport.started_at
                                            ? new Date(lastImport.started_at).toLocaleString()
                                            : '—'
                                    }
                                />
                                <Row
                                    label="Finished"
                                    value={
                                        lastImport.finished_at
                                            ? new Date(lastImport.finished_at).toLocaleString()
                                            : '—'
                                    }
                                />
                                {lastImport.summary && (
                                    <Row
                                        label="Summary"
                                        value={
                                            <code className="text-xs font-mono text-slate-700 bg-slate-50 px-1.5 py-0.5 rounded">
                                                {JSON.stringify(lastImport.summary)}
                                            </code>
                                        }
                                        wide
                                    />
                                )}
                                {lastImport.error && (
                                    <Row
                                        label="Error"
                                        value={<span className="text-rose-600">{lastImport.error}</span>}
                                        wide
                                    />
                                )}
                            </dl>
                        )}
                    </section>

                    <section className="bg-white shadow-sm rounded-xl border border-slate-200 p-6">
                        <h2 className="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                            <SquareStack className="h-4 w-4 text-slate-400" />
                            About this project
                        </h2>
                        <ul className="text-sm text-slate-700 space-y-2 list-disc pl-5">
                            <li>
                                Laravel 11 JSON API + standalone React (Vite + TanStack Query) SPA.
                            </li>
                            <li>
                                MDM provider abstraction (
                                <code className="text-xs bg-slate-100 px-1 py-0.5 rounded">
                                    app/Mdm/Contracts/MdmProvider
                                </code>
                                ) so adding a vendor — Kandji, Intune, etc. — is a localized change.
                            </li>
                            <li>
                                An import is a queued job; the UI subscribes to Laravel Reverb on{' '}
                                <code className="text-xs bg-slate-100 px-1 py-0.5 rounded">
                                    import.&#123;id&#125;
                                </code>{' '}
                                for live status. Redis backs the queue and cache.
                            </li>
                            <li>
                                Backend rules are covered by PHPUnit feature tests, one per MDM
                                Sync Behaviour Rule from the brief plus broadcast and action
                                assertions.
                            </li>
                        </ul>
                    </section>
                </div>

                <aside className="bg-white shadow-sm rounded-xl border border-slate-200 p-6 h-fit">
                    <h2 className="text-xs uppercase tracking-wide text-slate-500 mb-3">
                        Submitted by
                    </h2>
                    <div className="text-lg font-semibold text-slate-900">Maksym Kyslyi</div>
                    <div className="text-sm text-slate-600">
                        Full Stack Engineer (Laravel / React)
                    </div>
                    <a
                        href="https://www.linkedin.com/in/kisliymaxim/"
                        target="_blank"
                        rel="noreferrer noopener"
                        className="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        <Linkedin className="h-4 w-4" />
                        linkedin.com/in/kisliymaxim
                    </a>

                    <hr className="my-4 border-slate-200" />

                    <h3 className="text-xs uppercase tracking-wide text-slate-500 mb-2">
                        Case study
                    </h3>
                    <p className="text-sm text-slate-700">
                        WorkWize — MDM Device Sync. Imports assigned devices from Jamf into a
                        local store with idempotent re-imports, source-of-truth semantics, and an
                        admin UI for review.
                    </p>

                    <div className="mt-4 flex flex-col gap-2 text-sm">
                        <Link
                            to="/assets"
                            className="inline-flex items-center justify-between text-indigo-600 hover:text-indigo-800"
                        >
                            View assets <ArrowRight className="h-4 w-4" />
                        </Link>
                        <Link
                            to="/employees"
                            className="inline-flex items-center justify-between text-indigo-600 hover:text-indigo-800"
                        >
                            View employees <ArrowRight className="h-4 w-4" />
                        </Link>
                    </div>
                </aside>
            </div>
        </>
    );
}

function StatCard({
    label,
    value,
    loading,
    href,
    icon: Icon,
}: {
    label: string;
    value: number | undefined;
    loading: boolean;
    href?: string;
    icon: typeof Laptop;
}) {
    const body = (
        <div className="bg-white shadow-sm rounded-xl border border-slate-200 px-5 py-5 transition hover:border-indigo-300 hover:shadow">
            <div className="flex items-center justify-between">
                <div className="text-xs uppercase tracking-wide text-slate-500">{label}</div>
                <Icon className="h-4 w-4 text-slate-400" />
            </div>
            <div className="mt-2 text-3xl font-semibold text-slate-900 tabular-nums">
                {loading ? <Skeleton className="h-9 w-16" /> : (value ?? 0)}
            </div>
        </div>
    );
    return href ? (
        <Link to={href} className="block">
            {body}
        </Link>
    ) : (
        body
    );
}

function Row({
    label,
    value,
    wide = false,
}: {
    label: string;
    value: React.ReactNode;
    wide?: boolean;
}) {
    return (
        <>
            <dt className={`text-slate-500 ${wide ? 'sm:col-span-2' : ''}`}>{label}</dt>
            <dd className={`text-slate-900 ${wide ? 'sm:col-span-2 -mt-2' : ''}`}>{value}</dd>
        </>
    );
}
