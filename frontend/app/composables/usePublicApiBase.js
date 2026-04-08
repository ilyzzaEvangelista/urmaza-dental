import { resolveApiBaseString } from "~/utils/apiBase";

/**
 * Resolved API origin for `$fetch` / storage URLs. Reactive to runtime config.
 */
export function usePublicApiBase() {
    const config = useRuntimeConfig();
    return computed(() => resolveApiBaseString(config.public.apiBase));
}
