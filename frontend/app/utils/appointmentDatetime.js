/**
 * Must match backend `config('app.timezone')` / `APP_TIMEZONE` (default Asia/Manila).
 */
export const CLINIC_TIMEZONE = "Asia/Manila";

/**
 * @param {string|undefined|null} isoDate — ISO string from API (includes offset after backend fix)
 * @returns {{ dateLabel: string, timeLabel: string, rawLocal: string }}
 */
export function appointmentPartsFromApi(isoDate) {
    if (!isoDate) {
        return { dateLabel: "—", timeLabel: "", rawLocal: "" };
    }
    const d = new Date(isoDate);
    if (Number.isNaN(d.getTime())) {
        return { dateLabel: "—", timeLabel: "", rawLocal: "" };
    }
    const dateLabel = d.toLocaleDateString("en-PH", {
        timeZone: CLINIC_TIMEZONE,
        year: "numeric",
        month: "short",
        day: "numeric",
    });
    const timeLabel = d.toLocaleTimeString("en-PH", {
        timeZone: CLINIC_TIMEZONE,
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    });
    const rawLocal = instantToDatetimeLocalInTimezone(d, CLINIC_TIMEZONE);
    return { dateLabel, timeLabel, rawLocal };
}

/**
 * @param {Date} date
 * @param {string} timeZone — IANA, e.g. Asia/Manila
 * @returns {string} `YYYY-MM-DDTHH:mm` for `<input type="datetime-local">`
 */
export function instantToDatetimeLocalInTimezone(date, timeZone) {
    const s = date.toLocaleString("sv-SE", { timeZone });
    return s.replace(" ", "T").slice(0, 16);
}
