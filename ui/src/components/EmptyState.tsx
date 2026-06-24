import { LucideIcon } from 'lucide-react';
import { ReactNode } from 'react';

export default function EmptyState({
    icon: Icon,
    title,
    description,
    action,
}: {
    icon: LucideIcon;
    title: string;
    description?: ReactNode;
    action?: ReactNode;
}) {
    return (
        <div className="p-10 text-center">
            <div className="mx-auto h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                <Icon className="h-6 w-6" />
            </div>
            <h3 className="mt-4 text-sm font-semibold text-slate-800">{title}</h3>
            {description && (
                <p className="mt-1 text-sm text-slate-500 max-w-md mx-auto">{description}</p>
            )}
            {action && <div className="mt-4">{action}</div>}
        </div>
    );
}
