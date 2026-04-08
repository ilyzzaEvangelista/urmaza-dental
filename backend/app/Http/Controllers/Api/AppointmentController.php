<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
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
