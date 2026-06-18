import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

let echoInstance = null;
let lastToken = null;

/**
 * Get or initialize Laravel Echo instance dynamically with the latest Sanctum token.
 */
export const getEcho = () => {
  const token = localStorage.getItem("auth_token");

  // Recreate instance if token changed to ensure updated auth headers
  if (echoInstance && token === lastToken) {
    return echoInstance;
  }

  if (echoInstance) {
    echoInstance.disconnect();
  }

  lastToken = token;

  const baseUrl = import.meta.env.VITE_API_URL
    ? import.meta.env.VITE_API_URL.replace(/\/api$/, "")
    : "http://localhost:8000";

  echoInstance = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
    authEndpoint: `${baseUrl}/api/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: token ? `Bearer ${token}` : "",
        Accept: "application/json",
      },
    },
  });

  return echoInstance;
};
