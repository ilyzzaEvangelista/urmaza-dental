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
     * Check if the given minute slot already has an appointment.
     */
    protected function slotOccupied(Carbon $moment, ?int $exceptId = null): bool
    {
        $start = $moment->copy()->startOfMinute();
        $end = $moment->copy()->endOfMinute();

        $query = Appointment::query()
            ->whereBetween('appointment_date', [$start, $end]);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function availability(Request $request)
    {
        $request->validate([
            'datetime' => 'required|date',
            'except_id' => 'sometimes|integer|exists:appointments,id',
        ]);

        $moment = $this->normalizeAppointmentMoment($request->query('datetime'));
        $exceptId = $request->filled('except_id') ? (int) $request->query('except_id') : null;

        return response()->json([
            'available' => ! $this->slotOccupied($moment, $exceptId),
        ]);
    }

    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 100));

        $appointments = Appointment::query()
            ->latest('id')
            ->limit($limit)
            ->get();

        return response()->json($appointments);
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
