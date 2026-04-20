<?php

namespace App\Models;

use App\Enums\WaitlistStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    use HasFactory;

    protected $table = 'waitlist';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'preferred_date',
        'preferred_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'status' => WaitlistStatus::class,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}

