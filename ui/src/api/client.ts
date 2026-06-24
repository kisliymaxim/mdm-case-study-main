import axios from 'axios';

const baseURL = import.meta.env.VITE_API_URL ?? 'http://localhost:8080/api';

export const api = axios.create({
    baseURL,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export type ApiError = {
    message: string;
    error_code?: string;
    status: number;
};

export function asApiError(error: unknown): ApiError {
    if (axios.isAxiosError(error)) {
        const data = error.response?.data as { message?: string; error_code?: string } | undefined;
        return {
            message: data?.message ?? error.message,
            error_code: data?.error_code,
            status: error.response?.status ?? 0,
        };
    }
    return {
        message: error instanceof Error ? error.message : 'Unknown error',
        status: 0,
    };
}
