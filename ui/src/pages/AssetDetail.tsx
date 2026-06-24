import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Laptop, Trash2, User } from 'lucide-react';
import { Link, useNavigate, useParams } from 'react-router-dom';

import { deleteAsset, getAsset } from '@/api/assets';
import { asApiError } from '@/api/client';
import { useConfirm } from '@/components/ConfirmDialog';
import PageHeader from '@/components/PageHeader';
import { Skeleton } from '@/components/Skeleton';
import { useToast } from '@/components/Toast';

export default function AssetDetail() {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const toast = useToast();
    const { confirm, dialog } = useConfirm();

    const asset = useQuery({
        queryKey: ['assets', id],
        queryFn: () => getAsset(id!),
        enabled: !!id,
    });

    const remove = useMutation({
        mutationFn: () => deleteAsset(id!),
        onSuccess: () => {
            toast.push('Asset deleted.', 'success');
            queryClient.invalidateQueries({ queryKey: ['assets'] });
            queryClient.invalidateQueries({ queryKey: ['employees'] });
            queryClient.invalidateQueries({ queryKey: ['stats'] });
            navigate('/assets');
        },
        onError: (err) => toast.push(asApiError(err).message, 'error'),
    });

    if (asset.isLoading) {
        return (
            <>
                <PageHeader title="Asset Details" />
                <div className="bg-white shadow-sm rounded-xl border border-slate-200 p-6 space-y-5">
                    <Skeleton className="h-6 w-1/3" />
                    <Skeleton className="h-4 w-1/4" />
                    <Skeleton className="h-4 w-2/3" />
                    <Skeleton className="h-40 w-full" />
                </div>
            </>
        );
    }

    if (asset.isError || !asset.data) {
        return (
            <>
                <PageHeader title="Asset Details" />
                <div className="bg-white rounded-xl border border-rose-200 p-6 text-rose-600 text-sm">
                    {asset.error ? asApiError(asset.error).message : 'Not found.'}
                </div>
            </>
        );
    }

    const data = asset.data;
    const specs = data.specs ?? {};

    const onDelete = async () => {
        const ok = await confirm({
            title: `Delete "${data.device_name}"?`,
            description: `Serial ${data.serial_code}. This only removes the local row — re-sync will recreate it.`,
            confirmLabel: 'Delete',
            tone: 'danger',
        });
        if (ok) remove.mutate();
    };

    return (
        <>
            {dialog}
            <PageHeader
                title="Asset Details"
                actions={
                    <div className="flex gap-2 items-center">
                        <Link
                            to="/assets"
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-slate-600 hover:text-slate-900 rounded-md hover:bg-slate-100"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Back
                        </Link>
                        <button
                            onClick={onDelete}
                            disabled={remove.isPending}
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-rose-700 hover:text-white hover:bg-rose-600 border border-rose-200 rounded-md disabled:opacity-50 transition"
                        >
                            <Trash2 className="h-4 w-4" />
                            Delete
                        </button>
                    </div>
                }
            />

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    <section className="bg-white shadow-sm rounded-xl border border-slate-200 p-6">
                        <div className="flex items-center gap-4">
                            <div className="h-12 w-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <Laptop className="h-6 w-6" />
                            </div>
                            <div className="min-w-0">
                                <h2 className="text-lg font-semibold text-slate-900 truncate">
                                    {data.device_name}
                                </h2>
                                <div className="text-xs text-slate-500 font-mono">
                                    {data.serial_code} · {data.provider}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section className="bg-white shadow-sm rounded-xl border border-slate-200 p-6">
                        <h3 className="text-sm font-semibold text-slate-700 mb-3">
                            Asset Attributes
                        </h3>
                        <div className="overflow-hidden border border-slate-200 rounded-lg">
                            <table className="min-w-full text-sm">
                                <tbody className="divide-y divide-slate-200">
                                    {Object.keys(specs).length === 0 && (
                                        <tr>
                                            <td className="px-3 py-3 text-slate-500" colSpan={2}>
                                                No attributes reported.
                                            </td>
                                        </tr>
                                    )}
                                    {Object.entries(specs).map(([k, v]) => (
                                        <tr key={k} className="hover:bg-slate-50">
                                            <th className="px-3 py-2 bg-slate-50 text-left font-medium text-slate-600 w-1/3 capitalize">
                                                {k.replace(/_/g, ' ')}
                                            </th>
                                            <td className="px-3 py-2 text-slate-900">
                                                {v === null || v === undefined || v === ''
                                                    ? '—'
                                                    : String(v)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <aside className="bg-white shadow-sm rounded-xl border border-slate-200 p-6 h-fit">
                    <h3 className="text-xs uppercase tracking-wide text-slate-500 mb-3">
                        Assigned to
                    </h3>
                    {data.employee ? (
                        <div className="flex items-start gap-3">
                            <div className="h-10 w-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                <User className="h-5 w-5" />
                            </div>
                            <div className="min-w-0">
                                <div className="font-medium text-slate-900 truncate">
                                    {data.employee.name ?? data.employee.email}
                                </div>
                                <div className="text-sm text-slate-500 truncate">
                                    {data.employee.email}
                                </div>
                                {data.employee.phone && (
                                    <div className="text-sm text-slate-500">
                                        {data.employee.phone}
                                    </div>
                                )}
                            </div>
                        </div>
                    ) : (
                        <div className="text-sm text-slate-500">Unassigned</div>
                    )}
                </aside>
            </div>
        </>
    );
}
