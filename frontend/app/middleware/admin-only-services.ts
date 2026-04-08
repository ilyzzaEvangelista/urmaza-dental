/**
 * Services CRUD is admin-only; assistants use the rest of the staff area.
 */
export default defineNuxtRouteMiddleware(() => {
    const role = useCookie<string | null>("auth_role");

    if (role.value !== "admin") {
        return navigateTo("/admin");
    }
});
