<?php

namespace App\Models;

use App\Enums\AppointmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAvailability extends Model
{
    use HasFactory;

    protected $table = 'doctor_availability';

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'appointment_type',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_available' => 'boolean',
        'appointment_type' => AppointmentType::class,
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}

