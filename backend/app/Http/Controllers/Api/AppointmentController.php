<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        $perPage = 10;

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
        ]);

        $query = Appointment::query()
            ->orderByDesc('appointment_date')
            ->orderByDesc('id');

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
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
