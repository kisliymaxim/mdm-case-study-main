import { AlertCircle, CheckCircle2, Info, X } from 'lucide-react';
import {
    ReactNode,
    createContext,
    useCallback,
    useContext,
    useEffect,
    useState,
} from 'react';

type ToastKind = 'success' | 'error' | 'info';

type Toast = {
    id: number;
    kind: ToastKind;
    message: string;
};

type ToastApi = {
    push: (message: string, kind?: ToastKind) => void;
};

const ToastContext = createContext<ToastApi | null>(null);

const iconFor = (kind: ToastKind) =>
    kind === 'success' ? CheckCircle2 : kind === 'error' ? AlertCircle : Info;

const stylesFor = (kind: ToastKind) =>
    kind === 'success'
        ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
        : kind === 'error'
          ? 'bg-rose-50 border-rose-200 text-rose-800'
          : 'bg-slate-50 border-slate-200 text-slate-800';

export function ToastProvider({ children }: { children: ReactNode }) {
    const [toasts, setToasts] = useState<Toast[]>([]);

    const push = useCallback((message: string, kind: ToastKind = 'info') => {
        setToasts((prev) => [...prev, { id: Date.now() + Math.random(), kind, message }]);
    }, []);

    const dismiss = useCallback((id: number) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    }, []);

    useEffect(() => {
        if (toasts.length === 0) return;
        const last = toasts[toasts.length - 1];
        const timer = setTimeout(() => dismiss(last.id), 4500);
        return () => clearTimeout(timer);
    }, [toasts, dismiss]);

    return (
        <ToastContext.Provider value={{ push }}>
            {children}
            <div className="fixed top-4 right-4 z-[60] space-y-2 w-80">
                {toasts.map((t) => {
                    const Icon = iconFor(t.kind);
                    return (
                        <div
                            key={t.id}
                            role="status"
                            className={`flex items-start gap-2.5 px-3.5 py-3 rounded-lg border shadow-sm text-sm ${stylesFor(
                                t.kind,
                            )}`}
                        >
                            <Icon className="h-4 w-4 mt-0.5 shrink-0" />
                            <div className="flex-1 min-w-0">{t.message}</div>
                            <button
                                onClick={() => dismiss(t.id)}
                                className="text-slate-400 hover:text-slate-700"
                                aria-label="Dismiss"
                            >
                                <X className="h-3.5 w-3.5" />
                            </button>
                        </div>
                    );
                })}
            </div>
        </ToastContext.Provider>
    );
}

export function useToast(): ToastApi {
    const ctx = useContext(ToastContext);
    if (!ctx) throw new Error('useToast must be used inside <ToastProvider>');
    return ctx;
}
