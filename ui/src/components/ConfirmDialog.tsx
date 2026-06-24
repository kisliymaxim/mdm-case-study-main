import { AlertTriangle, X } from 'lucide-react';
import { ReactNode, useCallback, useEffect, useState } from 'react';
import { createPortal } from 'react-dom';

type ConfirmOptions = {
    title: string;
    description?: ReactNode;
    confirmLabel?: string;
    cancelLabel?: string;
    tone?: 'danger' | 'neutral';
};

type Pending = ConfirmOptions & {
    resolve: (ok: boolean) => void;
};

export function useConfirm() {
    const [pending, setPending] = useState<Pending | null>(null);

    const confirm = useCallback(
        (opts: ConfirmOptions) =>
            new Promise<boolean>((resolve) => {
                setPending({ ...opts, resolve });
            }),
        [],
    );

    const resolve = useCallback(
        (ok: boolean) => {
            pending?.resolve(ok);
            setPending(null);
        },
        [pending],
    );

    useEffect(() => {
        if (!pending) return;
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') resolve(false);
            if (e.key === 'Enter') resolve(true);
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [pending, resolve]);

    const dialog = pending
        ? createPortal(
              <div
                  className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
                  onClick={() => resolve(false)}
                  role="dialog"
                  aria-modal="true"
                  aria-labelledby="confirm-title"
              >
                  <div
                      className="bg-white rounded-xl shadow-xl border border-slate-200 max-w-sm w-full p-5 relative"
                      onClick={(e) => e.stopPropagation()}
                  >
                      <button
                          onClick={() => resolve(false)}
                          className="absolute top-3 right-3 text-slate-400 hover:text-slate-600"
                          aria-label="Close"
                      >
                          <X className="h-4 w-4" />
                      </button>

                      <div className="flex items-start gap-3">
                          {pending.tone === 'danger' && (
                              <div className="h-9 w-9 shrink-0 rounded-full bg-rose-50 flex items-center justify-center text-rose-600">
                                  <AlertTriangle className="h-5 w-5" />
                              </div>
                          )}
                          <div className="min-w-0">
                              <h2
                                  id="confirm-title"
                                  className="text-base font-semibold text-slate-900"
                              >
                                  {pending.title}
                              </h2>
                              {pending.description && (
                                  <p className="mt-1 text-sm text-slate-600">{pending.description}</p>
                              )}
                          </div>
                      </div>

                      <div className="mt-5 flex items-center justify-end gap-2">
                          <button
                              onClick={() => resolve(false)}
                              className="px-3 py-1.5 text-sm font-medium text-slate-700 rounded-md hover:bg-slate-100"
                          >
                              {pending.cancelLabel ?? 'Cancel'}
                          </button>
                          <button
                              onClick={() => resolve(true)}
                              autoFocus
                              className={`px-3 py-1.5 text-sm font-medium rounded-md text-white shadow-sm ${
                                  pending.tone === 'danger'
                                      ? 'bg-rose-600 hover:bg-rose-700'
                                      : 'bg-indigo-600 hover:bg-indigo-700'
                              }`}
                          >
                              {pending.confirmLabel ?? 'Confirm'}
                          </button>
                      </div>
                  </div>
              </div>,
              document.body,
          )
        : null;

    return { confirm, dialog };
}
