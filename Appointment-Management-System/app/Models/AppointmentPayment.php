<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'amount',
        'method',
        'stripe_payment_id',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'method' => PaymentMethod::class,
        'paid_at' => 'datetime',
        'status' => PaymentStatus::class,
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}

