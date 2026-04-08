/**
 * Headers for Laravel Sanctum Bearer requests (client-side cookie `auth_token`).
 */
function normalizeToken(raw: string | null | undefined): string {
    if (raw == null || raw === "") {
        return "";
    }
    let s = String(raw).trim();
    if ((s.startsWith('"') && s.endsWith('"')) || (s.startsWith("'") && s.endsWith("'"))) {
        s = s.slice(1, -1).trim();
    }
    return s;
}

export function useAuthFetchHeaders() {
    const token = useCookie<string | null>("auth_token");

    return computed(() => {
        const t = normalizeToken(token.value ?? "");
        if (!t) {
            return {};
        }
        return { Authorization: `Bearer ${t}` };
    });
}
