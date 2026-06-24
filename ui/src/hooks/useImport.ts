import { useMutation } from '@tanstack/react-query';
import { useCallback, useEffect, useRef, useState } from 'react';

import { asApiError } from '@/api/client';
import { triggerImport } from '@/api/imports';
import type { MdmImport } from '@/api/types';
import { echo } from '@/lib/echo';

export function useImport(onSucceeded?: () => void) {
    const [run, setRun] = useState<MdmImport | null>(null);
    const [lastError, setLastError] = useState<string | null>(null);

    const onSucceededRef = useRef(onSucceeded);
    onSucceededRef.current = onSucceeded;

    const firedForImportIdRef = useRef<string | null>(null);

    const trigger = useMutation({
        mutationFn: triggerImport,
        onMutate: () => {
            setLastError(null);
            setRun(null);
            firedForImportIdRef.current = null;
        },
        onSuccess: (initial) => setRun(initial),
        onError: (err) => setLastError(asApiError(err).message),
    });

    // Subscribe whenever we have an active, non-terminal import.
    useEffect(() => {
        if (!run) return;
        if (run.status === 'succeeded' || run.status === 'failed') return;

        const channel = echo.channel(`import.${run.id}`);
        channel.listen('.ImportUpdated', (payload: { import: MdmImport }) => {
            setRun(payload.import);
        });

        return () => {
            channel.stopListening('.ImportUpdated');
            echo.leaveChannel(`import.${run.id}`);
        };
    }, [run]);

    // Fire-once side effects per import id when terminal state arrives.
    useEffect(() => {
        if (!run) return;
        if (run.status === 'succeeded' && firedForImportIdRef.current !== run.id) {
            firedForImportIdRef.current = run.id;
            onSucceededRef.current?.();
        }
        if (run.status === 'failed' && !lastError) {
            setLastError(run.error ?? 'Import failed.');
        }
    }, [run, lastError]);

    const inFlight =
        trigger.isPending || (!!run && (run.status === 'queued' || run.status === 'running'));

    const reset = useCallback(() => {
        setRun(null);
        setLastError(null);
        firedForImportIdRef.current = null;
    }, []);

    return {
        trigger: () => trigger.mutate(),
        run,
        inFlight,
        error: lastError,
        reset,
    };
}
