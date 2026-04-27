<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Mail\EmailOtpMail;
use App\Models\Doctor;
use App\Models\EmailOtp;
use App\Models\Hospital;
use App\Models\MedicalProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'register_as' => ['required', Rule::in(['hospital_admin', 'doctor', 'patient'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'hospital_name' => ['required_if:register_as,hospital_admin', 'nullable', 'string', 'max:255'],
            'hospital_address' => ['nullable', 'string', 'max:500'],
            'hospital_phone' => ['nullable', 'string', 'max:50'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $role = RoleType::from($validated['register_as']);
            $status = $role === RoleType::Patient ? AccountStatus::Active : AccountStatus::Pending;

            $hospitalId = null;
            if ($role === RoleType::HospitalAdmin) {
                $hospital = Hospital::create([
                    'name' => $validated['hospital_name'],
                    'address' => $validated['hospital_address'] ?? null,
                    'phone' => $validated['hospital_phone'] ?? null,
                ]);

                $hospitalId = $hospital->id;
            }

            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'hospital_id' => $hospitalId,
                'role' => $role,
                'status' => $status,
            ]);

            if ($role === RoleType::Doctor) {
                Doctor::create([
                    'user_id' => $user->id,
                    'specialization_id' => $validated['specialization_id'] ?? null,
                    'license_number' => $validated['license_number'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                ]);
            }

            if ($role === RoleType::Patient) {
                MedicalProfile::create([
                    'user_id' => $user->id,
                ]);
            }

            return $user;
        });

        $this->generateAndSendOtp($user);

        return response()->json([
            'message' => 'Registration completed. Please verify your email with OTP.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => $user->status->value,
                'role' => $user->role->value,
            ],
        ], 201);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $otp = EmailOtp::where('user_id', $user->id)->latest('id')->first();

        if (! $otp) {
            return response()->json(['message' => 'OTP not found. Please request a new one.'], 404);
        }

        if ($otp->verified_at) {
            return response()->json(['message' => 'OTP already verified.'], 422);
        }

        if ($otp->expires_at->isPast()) {
            return response()->json(['message' => 'OTP expired. Please request a new one.'], 422);
        }

        if (! Hash::check($validated['otp'], $otp->otp_hash)) {
            return response()->json(['message' => 'Invalid OTP code.'], 422);
        }

        $otp->update(['verified_at' => now()]);
        $user->update(['email_verified_at' => now()]);

        return response()->json([
            'message' => 'Email verified successfully.',
            'data' => [
                'email_verified_at' => $user->fresh()->email_verified_at,
            ],
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.'], 422);
        }

        $this->generateAndSendOtp($user);

        return response()->json([
            'message' => 'A new OTP has been sent to your email.',
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        if (! $user->email_verified_at) {
            return response()->json(['message' => 'Please verify your email first.'], 403);
        }

        if ($user->status !== AccountStatus::Active) {
            return response()->json([
                'message' => 'Your account is pending admin approval.',
            ], 403);
        }

        $token = $user->createToken($validated['device_name'] ?? 'api')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->only(['id', 'first_name', 'last_name', 'email', 'role', 'status']),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function generateAndSendOtp(User $user): void
    {
        $plainOtp = (string) random_int(100000, 999999);

        EmailOtp::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make($plainOtp),
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new EmailOtpMail(
            fullName: trim("{$user->first_name} {$user->last_name}"),
            otpCode: $plainOtp
        ));
    }
}
