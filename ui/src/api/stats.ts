import { api } from '@/api/client';
import type { MdmImport } from '@/api/types';

export type Stats = {
    counts: {
        assets: number;
        employees: number;
        imports: number;
    };
    last_import: MdmImport | null;
};

export async function getStats(): Promise<Stats> {
    const { data } = await api.get<Stats>('/stats');
    return data;
}
