<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'avatar',
        'hospital_id',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'role' => RoleType::class,
        'status' => AccountStatus::class,
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function medicalProfile(): HasOne
    {
        return $this->hasOne(MedicalProfile::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(Waitlist::class, 'patient_id');
    }

    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(FileAttachment::class, 'uploaded_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
