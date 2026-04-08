<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AppointmentController extends Controller
{
    /**
     * Normalize request datetime strings (naive or ISO) to the app timezone.
     */
    protected function normalizeAppointmentMoment(string $value): Carbon
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->startOfMinute();
    }

    /**
     * Statuses that still reserve the time slot for new bookings.
     * Completed / cancelled / no-show visits do not block the slot.
     *
     * @return list<string>
     */
    protected function slotBlockingStatuses(): array
    {
        return ['pending', 'confirmed'];
    }

    /**
     * The appointment occupying this minute slot, if any (only pending/confirmed block).
     */
    protected function slotBlockingAppointment(Carbon $moment, ?int $exceptId = null): ?Appointment
    {
        $start = $moment->copy()->startOfMinute();
        $end = $moment->copy()->endOfMinute();

        $query = Appointment::query()
            ->whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', $this->slotBlockingStatuses());

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->first();
    }

    /**
     * Check if the given minute slot already has a blocking appointment.
     */
    protected function slotOccupied(Carbon $moment, ?int $exceptId = null): bool
    {
        return $this->slotBlockingAppointment($moment, $exceptId) !== null;
    }

    public function availability(Request $request)
    {
        $request->validate([
            'datetime' => 'required|date',
            'except_id' => 'sometimes|integer|exists:appointments,id',
        ]);

        $moment = $this->normalizeAppointmentMoment($request->query('datetime'));
        $exceptId = $request->filled('except_id') ? (int) $request->query('except_id') : null;

        $blocking = $this->slotBlockingAppointment($moment, $exceptId);

        return response()->json([
            'available' => $blocking === null,
            'blocked_by_pending' => $blocking !== null && strtolower((string) $blocking->status) === 'pending',
        ]);
    }

    /**
     * Distinct patients (by email) with at least one appointment in the given window.
     */
    protected function distinctPatientCount(Carbon $start, Carbon $end): int
    {
        $value = Appointment::query()
            ->whereBetween('appointment_date', [$start, $end])
            ->selectRaw('count(distinct email) as aggregate')
            ->value('aggregate');

        return (int) $value;
    }

    /**
     * Dashboard stats: unique patients (by email) for today, this week, and all time.
     */
    public function patientAnalytics()
    {
        $tz = config('app.timezone');
        $now = now()->timezone($tz);

        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        $patientsToday = $this->distinctPatientCount($todayStart, $todayEnd);
        $patientsThisWeek = $this->distinctPatientCount($weekStart, $weekEnd);

        $patientsTotal = (int) Appointment::query()
            ->selectRaw('count(distinct email) as aggregate')
            ->value('aggregate');

        return response()->json([
            'patients_today' => $patientsToday,
            'patients_this_week' => $patientsThisWeek,
            'patients_total' => $patientsTotal,
        ]);
    }

    /**
     * Appointments for a calendar week (Mon–Sun), for the admin week board.
     * Query: week_offset (int, default 0) — 0 = current week, -1 = previous, +1 = next.
     */
    public function week(Request $request)
    {
        $offset = (int) $request->query('week_offset', 0);

        $tz = config('app.timezone');
        $now = now()->timezone($tz);

        $anchor = $now->copy()->startOfWeek()->addWeeks($offset);
        $rangeStart = $anchor->copy()->startOfDay();
        $rangeEnd = $anchor->copy()->endOfWeek()->endOfDay();

        $days = [];
        $cursor = $rangeStart->copy();
        for ($i = 0; $i < 7; $i++) {
            $days[] = [
                'date' => $cursor->toDateString(),
                'weekday_short' => $cursor->format('D'),
                'day' => (int) $cursor->format('j'),
                'is_today' => $cursor->isSameDay($now),
            ];
            $cursor->addDay();
        }

        $appointments = Appointment::query()
            ->whereBetween('appointment_date', [$rangeStart, $rangeEnd])
            ->orderBy('appointment_date')
            ->get();

        $grouped = [];
        foreach ($appointments as $a) {
            $key = $a->appointment_date->clone()->timezone($tz)->format('Y-m-d');
            if (! isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $a;
        }
        ksort($grouped);

        return response()->json([
            'week_offset' => $offset,
            'week_start' => $rangeStart->toIso8601String(),
            'week_end' => $rangeEnd->toIso8601String(),
            'month_label' => $anchor->format('F Y'),
            'days' => $days,
            'grouped_appointments' => $grouped,
        ]);
    }

    /**
     * Days in a calendar month that have reserved (pending) or confirmed appointments.
     * Query: year (int), month (1–12).
     *
     * @return array{year: int, month: int, marked_dates: array<string, array{pending: bool, confirmed: bool}>, appointments_by_date: array<string, list<array{name: string, service: string, status: string}>>}
     */
    public function calendarMonth(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $tz = config('app.timezone');
        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $start = Carbon::create($year, $month, 1, 0, 0, 0, $tz)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $appointments = Appointment::query()
            ->whereBetween('appointment_date', [$start, $end])
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date')
            ->get(['appointment_date', 'status', 'name', 'service']);

        $marked = [];
        $byDate = [];
        foreach ($appointments as $a) {
            $key = $a->appointment_date->clone()->timezone($tz)->format('Y-m-d');
            if (! isset($marked[$key])) {
                $marked[$key] = ['pending' => false, 'confirmed' => false];
            }
            $s = strtolower((string) $a->status);
            if ($s === 'pending') {
                $marked[$key]['pending'] = true;
            }
            if ($s === 'confirmed') {
                $marked[$key]['confirmed'] = true;
            }
            if (! isset($byDate[$key])) {
                $byDate[$key] = [];
            }
            $byDate[$key][] = [
                'name' => $a->name,
                'service' => $a->service,
                'status' => $s,
            ];
        }
        ksort($marked);

        return response()->json([
            'year' => $year,
            'month' => $month,
            'marked_dates' => $marked,
            'appointments_by_date' => $byDate,
        ]);
    }

    /**
     * Patients derived from appointments, grouped by email (one patient can have many visits).
     * Query: page, per_page (max 50), search (optional — filters by name or email).
     */
    public function patients(Request $request)
    {
        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'search' => 'nullable|string|max:255',
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($validated['per_page'] ?? 10)));
        $search = trim((string) ($validated['search'] ?? ''));

        $appointments = Appointment::query()
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->get();

        $byEmail = $appointments->groupBy(fn (Appointment $a) => strtolower(trim((string) $a->email)));

        $patients = $byEmail->map(function ($rows) {
            $sorted = $rows->sortByDesc(fn (Appointment $a) => $a->appointment_date->timestamp)->values();
            $latest = $sorted->first();

            return [
                'email' => $latest->email,
                'name' => $latest->name,
                'contact_number' => $latest->contact_number,
                'age' => $latest->age,
                'appointment_count' => $sorted->count(),
                'appointments' => $sorted->map(fn (Appointment $a) => [
                    'id' => $a->id,
                    'appointment_date' => $a->appointment_date,
                    'service' => $a->service,
                    'status' => $a->status,
                    'note' => $a->note,
                    'doctor_comment' => $a->doctor_comment,
                ])->values()->all(),
            ];
        })->values();

        $patients = $patients->sortByDesc(function (array $p) {
            $dt = $p['appointments'][0]['appointment_date'] ?? null;

            return $dt instanceof Carbon ? $dt->timestamp : 0;
        })->values();

        if ($search !== '') {
            $needle = strtolower($search);
            $patients = $patients->filter(function (array $p) use ($needle) {
                return str_contains(strtolower((string) $p['name']), $needle)
                    || str_contains(strtolower((string) $p['email']), $needle);
            })->values();
        }

        $total = $patients->count();
        $slice = $patients->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return $paginator->withQueryString();
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Appointment::query()
            ->orderByDesc('appointment_date')
            ->orderByDesc('id');

        $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];

        if ($request->has('status')) {
            $raw = $request->query('status');
            if (is_array($raw)) {
                $request->validate([
                    'status' => 'sometimes|array',
                    'status.*' => 'string|in:pending,confirmed,completed,cancelled',
                ]);
                $statuses = collect($raw)
                    ->map(fn ($s) => strtolower((string) $s))
                    ->unique()
                    ->intersect($allowed)
                    ->values()
                    ->all();
                if (count($statuses) > 0) {
                    $query->whereIn('status', $statuses);
                }
            } else {
                $validated = $request->validate([
                    'status' => 'sometimes|string|in:pending,confirmed,completed,cancelled',
                ]);
                if (isset($validated['status'])) {
                    $query->where('status', $validated['status']);
                }
            }
        }

        return $query->paginate($perPage);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer',
            'email' => 'required|email',
            'contact_number' => 'required|string',
            'service' => 'required|string',
            'appointment_date' => 'required|date',
            'note' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('appointments', 'public');
            $validated['image'] = $path;
        }

        $validated['appointment_date'] = $this->normalizeAppointmentMoment($validated['appointment_date']);

        if ($this->slotOccupied($validated['appointment_date'])) {
            return response()->json([
                'message' => 'That date and time is already reserved. Please choose another slot.',
            ], 422);
        }

        $appointment = Appointment::create($validated);

        return response()->json([
            'message' => 'Appointment created successfully!',
            'data' => $appointment
        ], 201);
    }

    public function show(Appointment $appointment)
    {
        return response()->json($appointment);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:pending,confirmed,completed,cancelled',
            'name' => 'sometimes|string|max:255',
            'age' => 'sometimes|integer|min:1|max:150',
            'email' => 'sometimes|email',
            'contact_number' => 'sometimes|string|max:255',
            'service' => 'sometimes|string|max:255',
            'appointment_date' => 'sometimes|date',
            'note' => 'nullable|string',
            'doctor_comment' => 'nullable|string',
        ]);

        if (array_key_exists('appointment_date', $validated)) {
            $validated['appointment_date'] = $this->normalizeAppointmentMoment($validated['appointment_date']);

            if ($this->slotOccupied($validated['appointment_date'], $appointment->id)) {
                return response()->json([
                    'message' => 'That date and time is already reserved. Please choose another slot.',
                ], 422);
            }
        }

        $appointment->update($validated);

        return response()->json([
            'message' => 'Appointment updated successfully!',
            'data' => $appointment
        ]);
    }

    public function destroy(Appointment $appointment)
    {
        // Soft delete: row is hidden via deleted_at; image kept for possible restore.
        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully!'
        ]);
    }
}
