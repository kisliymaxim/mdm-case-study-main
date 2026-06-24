import { Loader2, RefreshCw } from 'lucide-react';

export default function ImportButton({
    onClick,
    inFlight,
}: {
    onClick: () => void;
    inFlight: boolean;
}) {
    return (
        <button
            onClick={onClick}
            disabled={inFlight}
            className="group relative inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white text-sm font-medium rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:ring-offset-2"
        >
            {inFlight ? (
                <>
                    <Loader2 className="h-4 w-4 animate-spin" />
                    <span>Importing…</span>
                </>
            ) : (
                <>
                    <RefreshCw className="h-4 w-4 transition group-hover:rotate-180" />
                    <span>Run Import</span>
                </>
            )}
        </button>
    );
}
