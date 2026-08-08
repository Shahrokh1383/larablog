import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { sanctumClient } from '@/shared/api/httpClient';
import { env } from '@/shared/lib/env';

declare global {
  interface Window {
    Pusher: typeof Pusher;
  }
}

let echo: Echo<any> | null = null;

if (typeof window !== 'undefined') {
  window.Pusher = Pusher;

  echo = new Echo({
    broadcaster: 'reverb',
    key: env.reverbAppKey,
    wsHost: env.reverbHost,
    wsPort: parseInt(env.reverbPort, 10),
    wssPort: parseInt(env.reverbPort, 10), // Prevents fallback to 443
    forceTLS: false,
    enabledTransports: ['ws'], // Strictly local WS
    disableStats: true,
    authorizer: (channel) => {
      return {
        authorize: (socketId, callback) => {
          sanctumClient
            .post('/broadcasting/auth', {
              socket_id: socketId,
              channel_name: channel.name,
            })
            .then((response) => callback(null, response.data))
            .catch((error) => callback(error, null));
        },
      };
    },
  });
}

export default echo;