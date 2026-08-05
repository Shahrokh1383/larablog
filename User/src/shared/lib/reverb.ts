import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { env } from './env';

declare global {
  interface Window {
    Pusher: typeof Pusher;
  }
}

if (typeof window !== 'undefined') {
  window.Pusher = Pusher;
}

const port = parseInt(env.reverbPort, 10);

const echo = new Echo({
  broadcaster: 'reverb',
  key: env.reverbAppKey,
  wsHost: env.reverbHost,
  wsPort: port,
  wssPort: port,
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
});

export default echo;