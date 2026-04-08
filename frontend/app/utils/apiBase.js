/**
 * Laravel public URL (no trailing slash).
 * - Production: `""` = same origin as the SPA (set when API is served behind the same host).
 * - Development: empty `NUXT_PUBLIC_API_BASE` resolves to Laravel on port 8000 so the browser
 *   does not hit Nuxt for `/api/*` (Nuxt 4 often returns 404 for those unless a proxy is wired).
 *
 * @param {string | undefined | null} raw — from `runtimeConfig.public.apiBase` or prop
 */
export function resolveApiBaseString(raw) {
    if (raw === "" || raw === "/") {
        if (import.meta.dev) {
            return "http://127.0.0.1:8000";
        }
        return "";
    }
    if (raw != null && String(raw).trim() !== "") {
        return String(raw).replace(/\/$/, "");
    }
    return "http://127.0.0.1:8000";
}
