<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
        ];
    }

    /**
     * ISO-8601 in the app timezone so clients always get an explicit offset (e.g. +08:00).
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return Carbon::instance($date)
            ->timezone(config('app.timezone'))
            ->toIso8601String();
    }
}
