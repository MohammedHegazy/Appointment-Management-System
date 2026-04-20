<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blood_type',
        'allergies',
        'chronic_conditions',
        'medications',
        'emergency_contact',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

