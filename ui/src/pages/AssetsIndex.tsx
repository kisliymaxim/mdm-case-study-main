import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ChevronRight, Laptop, Trash2 } from 'lucide-react';
import { useCallback } from 'react';
import { Link } from 'react-router-dom';

import { deleteAsset, listAssets } from '@/api/assets';
import { asApiError } from '@/api/client';
import type { AssetSummary } from '@/api/types';
import { useConfirm } from '@/components/ConfirmDialog';
import EmptyState from '@/components/EmptyState';
import ImportButton from '@/components/ImportButton';
import PageHeader from '@/components/PageHeader';
import { SkeletonList } from '@/components/Skeleton';
import { useToast } from '@/components/Toast';
import { useImport } from '@/hooks/useImport';

export default function AssetsIndex() {
    const toast = useToast();
    const queryClient = useQueryClient();
    const { confirm, dialog } = useConfirm();

    const assets = useQuery({ queryKey: ['assets'], queryFn: listAssets });

    const invalidate = useCallback(() => {
        queryClient.invalidateQueries({ queryKey: ['assets'] });
        queryClient.invalidateQueries({ queryKey: ['employees'] });
        queryClient.invalidateQueries({ queryKey: ['stats'] });
    }, [queryClient]);

    const importRun = useImport(() => {
        toast.push('Import complete.', 'success');
        invalidate();
    });

    const remove = useMutation({
        mutationFn: deleteAsset,
        onSuccess: () => {
            toast.push('Asset deleted.', 'success');
            invalidate();
        },
        onError: (err) => toast.push(asApiError(err).message, 'error'),
    });

    const onDelete = async (asset: AssetSummary) => {
        const ok = await confirm({
            title: `Delete "${asset.device_name}"?`,
            description: `Serial ${asset.serial_code}. This only removes the local row — the next import will recreate it.`,
            confirmLabel: 'Delete',
            tone: 'danger',
        });
        if (!ok) return;
        remove.mutate(asset.id);
    };

    return (
        <>
            {dialog}
            <PageHeader
                title="Asset Management"
                subtitle={
                    assets.data
                        ? `${assets.data.length} device${assets.data.length === 1 ? '' : 's'} imported from Jamf`
                        : 'Devices imported from MDM providers'
                }
                actions={<ImportButton onClick={importRun.trigger} inFlight={importRun.inFlight} />}
            />

            {importRun.error && (
                <div className="mb-4 px-4 py-3 rounded-lg border bg-rose-50 border-rose-200 text-rose-800 text-sm">
                    {importRun.error}
                </div>
            )}

            <div className="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                {assets.isLoading && <SkeletonList rows={6} />}

                {assets.isError && (
                    <div className="p-8 text-center text-rose-600 text-sm">
                        {asApiError(assets.error).message}
                    </div>
                )}

                {assets.data && assets.data.length === 0 && (
                    <EmptyState
                        icon={Laptop}
                        title="No assets yet"
                        description="Click Run Import to pull assigned devices from Jamf."
                        action={
                            <ImportButton onClick={importRun.trigger} inFlight={importRun.inFlight} />
                        }
                    />
                )}

                {assets.data && assets.data.length > 0 && (
                    <ul className="divide-y divide-slate-200">
                        {assets.data.map((asset) => (
                            <li key={asset.id} className="group flex items-stretch hover:bg-slate-50">
                                <Link
                                    to={`/assets/${asset.id}`}
                                    className="flex-1 flex items-center min-w-0 px-4 py-3.5 gap-4"
                                >
                                    <div className="h-9 w-9 shrink-0 rounded-lg bg-slate-100 group-hover:bg-indigo-50 text-slate-500 group-hover:text-indigo-600 flex items-center justify-center transition">
                                        <Laptop className="h-5 w-5" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <div className="font-medium text-slate-900 truncate">
                                            {asset.device_name}
                                        </div>
                                        <div className="text-xs text-slate-500 font-mono">
                                            {asset.serial_code} · {asset.provider}
                                        </div>
                                    </div>
                                    <div className="hidden md:flex flex-col items-end shrink-0 text-right">
                                        <div className="text-xs uppercase tracking-wide text-slate-400">
                                            Assigned to
                                        </div>
                                        <div className="text-sm text-slate-700 font-medium">
                                            {asset.employee?.name || asset.employee?.email || '—'}
                                        </div>
                                    </div>
                                    <ChevronRight className="h-4 w-4 text-slate-300 group-hover:text-slate-500" />
                                </Link>
                                <button
                                    onClick={() => onDelete(asset)}
                                    disabled={remove.isPending}
                                    className="px-4 text-slate-400 hover:text-rose-600 disabled:opacity-40 transition"
                                    aria-label={`Delete ${asset.device_name}`}
                                    title="Delete asset"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}
