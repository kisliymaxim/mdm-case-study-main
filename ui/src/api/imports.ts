import { api } from '@/api/client';
import type { MdmImport } from '@/api/types';

export async function triggerImport(): Promise<MdmImport> {
    const { data } = await api.post<{ import: MdmImport }>('/imports');
    return data.import;
}

export async function getImport(id: string): Promise<MdmImport> {
    const { data } = await api.get<{ import: MdmImport }>(`/imports/${id}`);
    return data.import;
}
