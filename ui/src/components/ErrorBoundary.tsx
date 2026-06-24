import { AlertOctagon } from 'lucide-react';
import { Component, ErrorInfo, ReactNode } from 'react';

type Props = { children: ReactNode };
type State = { error: Error | null };

export default class ErrorBoundary extends Component<Props, State> {
    state: State = { error: null };

    static getDerivedStateFromError(error: Error): State {
        return { error };
    }

    componentDidCatch(error: Error, info: ErrorInfo): void {
        // eslint-disable-next-line no-console
        console.error('[ErrorBoundary]', error, info.componentStack);
    }

    private reload = () => window.location.reload();

    render() {
        if (!this.state.error) return this.props.children;

        return (
            <div className="min-h-screen bg-slate-50 flex items-center justify-center px-4">
                <div className="max-w-md w-full bg-white border border-rose-200 rounded-xl shadow-sm p-6">
                    <div className="flex items-start gap-3">
                        <div className="h-10 w-10 shrink-0 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center">
                            <AlertOctagon className="h-5 w-5" />
                        </div>
                        <div className="min-w-0">
                            <h1 className="text-base font-semibold text-slate-900">
                                Something went wrong
                            </h1>
                            <p className="mt-1 text-sm text-slate-600">
                                The UI hit an unexpected error and can't continue. Reloading the
                                page usually fixes it.
                            </p>
                            <pre className="mt-3 text-xs text-rose-700 bg-rose-50 border border-rose-100 rounded p-2 overflow-auto max-h-32">
                                {this.state.error.message}
                            </pre>
                            <button
                                onClick={this.reload}
                                className="mt-4 inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md"
                            >
                                Reload page
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}
