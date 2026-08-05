import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { env } from './env';
import { sanctumClient } from '@/shared/api/httpClient'; 

declare global {
  interface Window {
    Pusher: typeof Pusher;
  }
}

let echo: Echo<any> | null = null;

if (typeof window !== 'undefined') {
  window.Pusher = Pusher;

  const port = parseInt(env.reverbPort, 10) || 8080;

  echo = new Echo({
    broadcaster: 'reverb',
    key: env.reverbAppKey,
    wsHost: env.reverbHost || 'localhost',
    wsPort: port,
    wsPath: '/app', 
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    authorizer: (channel) => {
      return {
        authorize: (socketId, callback) => {
          sanctumClient.post('/broadcasting/auth', {
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