<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_type',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_type' => AppointmentType::class,
        'scheduled_at' => 'datetime',
        'status' => AppointmentStatus::class,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(AppointmentPayment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }
}
