<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'appointment_status_history';

    const UPDATED_AT = null;

    protected $fillable = [
        'appointment_id',
        'old_status',
        'new_status',
        'notes',
    ];

    protected $casts = [
        'old_status' => AppointmentStatus::class,
        'new_status' => AppointmentStatus::class,
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}

