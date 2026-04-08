/**
 * Headers for Laravel Sanctum Bearer requests (client-side cookie `auth_token`).
 */
export function useAuthFetchHeaders() {
    const token = useCookie<string | null>("auth_token");

    return computed(() =>
        token.value ? { Authorization: `Bearer ${token.value}` } : {},
    );
}
