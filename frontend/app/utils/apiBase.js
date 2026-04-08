/**
 * Laravel public URL (no trailing slash).
 * Use "" for same-origin requests (Nitro `devProxy` in development).
 *
 * @param {string | undefined | null} raw — from `runtimeConfig.public.apiBase` or prop
 */
export function resolveApiBaseString(raw) {
    if (raw === "" || raw === "/") {
        return "";
    }
    if (raw != null && String(raw).trim() !== "") {
        return String(raw).replace(/\/$/, "");
    }
    return "http://127.0.0.1:8000";
}
