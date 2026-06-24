import { ReactNode } from 'react';

export default function PageHeader({
    title,
    subtitle,
    actions,
}: {
    title: string;
    subtitle?: ReactNode;
    actions?: ReactNode;
}) {
    return (
        <div className="mb-5 flex items-start justify-between gap-4">
            <div>
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">{title}</h1>
                {subtitle && <p className="mt-1 text-sm text-slate-500">{subtitle}</p>}
            </div>
            {actions && <div className="shrink-0">{actions}</div>}
        </div>
    );
}
