import { api } from '@/api/client';
import type { EmployeeWithCount } from '@/api/types';

export async function listEmployees(): Promise<EmployeeWithCount[]> {
    const { data } = await api.get<{ data: EmployeeWithCount[] }>('/employees');
    return data.data;
}

export async function deleteEmployee(id: number | string): Promise<void> {
    await api.delete(`/employees/${id}`);
}
