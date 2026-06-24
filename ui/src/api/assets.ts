import { api } from '@/api/client';
import type { AssetDetail, AssetSummary } from '@/api/types';

export async function listAssets(): Promise<AssetSummary[]> {
    const { data } = await api.get<{ data: AssetSummary[] }>('/assets');
    return data.data;
}

export async function getAsset(id: number | string): Promise<AssetDetail> {
    const { data } = await api.get<{ data: AssetDetail }>(`/assets/${id}`);
    return data.data;
}

export async function deleteAsset(id: number | string): Promise<void> {
    await api.delete(`/assets/${id}`);
}
