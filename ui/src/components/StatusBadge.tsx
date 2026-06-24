import { CheckCircle2, CircleAlert, Clock, Loader2 } from 'lucide-react';

import type { ImportStatus } from '@/api/types';

const map: Record<
    ImportStatus,
    { label: string; classes: string; Icon: typeof CheckCircle2; spin?: boolean }
> = {
    queued: {
        label: 'Queued',
        classes: 'bg-slate-100 text-slate-700 border-slate-200',
        Icon: Clock,
    },
    running: {
        label: 'Running',
        classes: 'bg-amber-50 text-amber-800 border-amber-200',
        Icon: Loader2,
        spin: true,
    },
    succeeded: {
        label: 'Succeeded',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        Icon: CheckCircle2,
    },
    failed: {
        label: 'Failed',
        classes: 'bg-rose-50 text-rose-700 border-rose-200',
        Icon: CircleAlert,
    },
};

export default function StatusBadge({ status }: { status: ImportStatus | string }) {
    const cfg = map[status as ImportStatus] ?? {
        label: status,
        classes: 'bg-slate-100 text-slate-700 border-slate-200',
        Icon: Clock,
    };
    return (
        <span
            className={`inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full border text-xs font-medium ${cfg.classes}`}
        >
            <cfg.Icon className={`h-3 w-3 ${cfg.spin ? 'animate-spin' : ''}`} />
            {cfg.label}
        </span>
    );
}
