import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Pusher-protocol client wired to our Reverb server. Reverb speaks the
 * Pusher wire format, so `pusher-js` works as the transport and we get
 * Laravel Echo's nice channel API on top.
 *
 * Keys come from Vite env (set by docker-compose for the ui service).
 */
declare global {
    interface Window {
        Pusher: typeof Pusher;
    }
}

window.Pusher = Pusher;

const key = import.meta.env.VITE_REVERB_APP_KEY ?? '';
const host = import.meta.env.VITE_REVERB_HOST ?? 'localhost';
const port = Number(import.meta.env.VITE_REVERB_PORT ?? 8081);
const scheme = (import.meta.env.VITE_REVERB_SCHEME ?? 'http') as 'http' | 'https';

export const echo = new Echo({
    broadcaster: 'reverb',
    key,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});
