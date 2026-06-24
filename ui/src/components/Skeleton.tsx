export function Skeleton({ className = '' }: { className?: string }) {
    return (
        <div
            className={`animate-pulse rounded-md bg-slate-200/70 ${className}`}
            aria-hidden="true"
        />
    );
}

export function SkeletonRow() {
    return (
        <li className="flex items-center justify-between px-4 py-4">
            <div className="flex-1 min-w-0 space-y-2">
                <Skeleton className="h-4 w-2/3" />
                <Skeleton className="h-3 w-1/3" />
            </div>
            <Skeleton className="h-4 w-32 hidden sm:block" />
            <Skeleton className="h-4 w-12 ml-4" />
        </li>
    );
}

export function SkeletonList({ rows = 5 }: { rows?: number }) {
    return (
        <ul className="divide-y divide-slate-200">
            {Array.from({ length: rows }, (_, i) => (
                <SkeletonRow key={i} />
            ))}
        </ul>
    );
}
