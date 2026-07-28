/**
 * Laravel public URL (no trailing slash).
 * Empty = same origin as the SPA (`/api` and `/storage` via Vite/Nitro proxies in `nuxt.config.ts`).
 * Set `NUXT_PUBLIC_API_BASE` only when the API is on a different host.
 *
 * @param {string | undefined | null} raw — from `runtimeConfig.public.apiBase` or prop
 */
export function resolveApiBaseString(raw) {
    if (raw != null && String(raw).trim() !== "" && raw !== "/") {
        return String(raw).replace(/\/$/, "");
    }
    return "";
}