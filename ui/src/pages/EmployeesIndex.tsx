import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Mail, Trash2, User, Users } from 'lucide-react';

import { asApiError } from '@/api/client';
import { deleteEmployee, listEmployees } from '@/api/employees';
import type { EmployeeWithCount } from '@/api/types';
import { useConfirm } from '@/components/ConfirmDialog';
import EmptyState from '@/components/EmptyState';
import PageHeader from '@/components/PageHeader';
import { SkeletonList } from '@/components/Skeleton';
import { useToast } from '@/components/Toast';

export default function EmployeesIndex() {
    const toast = useToast();
    const queryClient = useQueryClient();
    const { confirm, dialog } = useConfirm();

    const employees = useQuery({ queryKey: ['employees'], queryFn: listEmployees });

    const remove = useMutation({
        mutationFn: (id: number) => deleteEmployee(id),
        onSuccess: () => {
            toast.push('Employee deleted.', 'success');
            queryClient.invalidateQueries({ queryKey: ['employees'] });
            queryClient.invalidateQueries({ queryKey: ['stats'] });
        },
        onError: (err) => toast.push(asApiError(err).message, 'error'),
    });

    const onDelete = async (employee: EmployeeWithCount) => {
        if (employee.assets_count > 0) {
            toast.push(
                `Cannot delete ${employee.email}: ${employee.assets_count} asset(s) still assigned.`,
                'error',
            );
            return;
        }
        const ok = await confirm({
            title: `Delete ${employee.email}?`,
            description: 'Re-sync will recreate the employee if Jamf still reports them.',
            confirmLabel: 'Delete',
            tone: 'danger',
        });
        if (ok) remove.mutate(employee.id);
    };

    return (
        <>
            {dialog}
            <PageHeader
                title="Employees"
                subtitle={
                    employees.data
                        ? `${employees.data.length} employee${employees.data.length === 1 ? '' : 's'} with assigned devices`
                        : 'People assigned to MDM-managed devices'
                }
            />

            <div className="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                {employees.isLoading && <SkeletonList rows={5} />}

                {employees.isError && (
                    <div className="p-8 text-center text-rose-600 text-sm">
                        {asApiError(employees.error).message}
                    </div>
                )}

                {employees.data && employees.data.length === 0 && (
                    <EmptyState
                        icon={Users}
                        title="No employees yet"
                        description="Run a sync from the Home or Assets page to import employees from Jamf."
                    />
                )}

                {employees.data && employees.data.length > 0 && (
                    <ul className="divide-y divide-slate-200">
                        {employees.data.map((employee) => (
                            <li
                                key={employee.id}
                                className="group flex items-center hover:bg-slate-50 px-4 py-3.5 gap-4"
                            >
                                <div className="h-9 w-9 shrink-0 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                                    <User className="h-5 w-5" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <div className="font-medium text-slate-900 truncate">
                                        {employee.name ?? employee.email}
                                    </div>
                                    <div className="text-xs text-slate-500 inline-flex items-center gap-1.5">
                                        <Mail className="h-3 w-3" />
                                        {employee.email}
                                        {employee.phone ? ` · ${employee.phone}` : ''}
                                    </div>
                                </div>
                                <div className="flex items-center gap-4 shrink-0">
                                    <span
                                        className={`text-xs px-2 py-0.5 rounded-full border ${
                                            employee.assets_count > 0
                                                ? 'bg-amber-50 text-amber-800 border-amber-200'
                                                : 'bg-slate-100 text-slate-600 border-slate-200'
                                        }`}
                                    >
                                        {employee.assets_count} asset
                                        {employee.assets_count === 1 ? '' : 's'}
                                    </span>
                                    <button
                                        onClick={() => onDelete(employee)}
                                        disabled={
                                            employee.assets_count > 0 || remove.isPending
                                        }
                                        title={
                                            employee.assets_count > 0
                                                ? 'Delete assigned assets first'
                                                : 'Delete employee'
                                        }
                                        className="text-slate-400 hover:text-rose-600 disabled:text-slate-300 disabled:cursor-not-allowed transition"
                                        aria-label={`Delete ${employee.email}`}
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}
