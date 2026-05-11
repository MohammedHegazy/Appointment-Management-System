<?php

namespace App\Http\Controllers\Web;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Mail\EmailOtpMail;
use App\Models\Doctor;
use App\Models\EmailOtp;
use App\Models\Hospital;
use App\Models\MedicalProfile;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthWebController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'specializations' => Specialization::query()->orderBy('name')->get(),
        ]);
    }

    public function register(Request $request): RedirectResponse
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
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bio' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')->store('doctor-certificates', 'public');
        }

        $user = DB::transaction(function () use ($validated): User {
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
                    'certificate_file' => $validated['certificate_file'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                ]);
            }

            if ($role === RoleType::Patient) {
                MedicalProfile::create(['user_id' => $user->id]);
            }

            return $user;
        });

        $this->generateAndSendOtp($user, $request);
        $this->auditLogService->log(
            action: 'user.registered.web',
            entity: $user,
            newValues: [
                'role' => $user->role->value,
                'status' => $user->status->value,
                'email' => $user->email,
                'doctor_certificate_uploaded' => ! empty($validated['certificate_file']),
            ],
            request: $request,
            userId: $user->id
        );

        return redirect()
            ->route('verify.form', ['email' => $user->email])
            ->with('success', 'Registration completed. We sent an OTP to your email.');
    }

    public function showVerifyOtp(Request $request): View
    {
        return view('auth.verify-otp', [
            'email' => (string) $request->query('email', old('email', '')),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $otp = EmailOtp::where('user_id', $user->id)->latest('id')->first();

        if (! $otp) {
            return back()->withInput()->withErrors(['otp' => 'OTP not found. Please request a new one.']);
        }

        if ($otp->verified_at) {
            return back()->withInput()->withErrors(['otp' => 'OTP already verified.']);
        }

        if ($otp->expires_at->isPast()) {
            return back()->withInput()->withErrors(['otp' => 'OTP expired. Please request a new one.']);
        }

        if (! Hash::check($validated['otp'], $otp->otp_hash)) {
            return back()->withInput()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        $otp->update(['verified_at' => now()]);
        $user->update(['email_verified_at' => now()]);
        $this->auditLogService->log(
            action: 'user.otp_verified.web',
            entity: $user,
            newValues: ['email_verified_at' => $user->fresh()->email_verified_at?->toDateTimeString()],
            request: $request,
            userId: $user->id
        );

        return redirect()
            ->route('login')
            ->with('success', 'Email verified successfully. You can now login.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        if ($user->email_verified_at) {
            return back()->withInput()->withErrors(['email' => 'Email is already verified.']);
        }

        $this->generateAndSendOtp($user, $request);
        $this->auditLogService->log(
            action: 'user.otp_resent.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );

        return back()->withInput()->with('success', 'A new OTP has been sent to your email.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $this->auditLogService->log(
                action: 'auth.login_failed.web',
                entity: 'User',
                entityId: $user?->id ?? 0,
                newValues: ['email' => $validated['email']],
                request: $request
            );
            return back()->withInput()->withErrors(['email' => 'Invalid email or password.']);
        }

        if (! $user->email_verified_at) {
            return redirect()
                ->route('verify.form', ['email' => $user->email])
                ->withErrors(['email' => 'Please verify your email first.']);
        }

        if ($user->status !== AccountStatus::Active) {
            return back()->withInput()->withErrors(['email' => 'Your account is pending admin approval.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->auditLogService->log(
            action: 'auth.login_success.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );

        $redirectRoleKey = $user->role->value;
        if ($redirectRoleKey === 'doctor' && $user->hospital_id !== null) {
            $redirectRoleKey = 'hospital_doctor';
        }

        $redirectRoute = (string) config('dashboard.role_home_routes.' . $redirectRoleKey, 'home');
        if (! Route::has($redirectRoute)) {
            $redirectRoute = 'home';
        }

        return redirect()->route($redirectRoute)->with('success', 'Welcome back!');
    }

    public function home(): View
    {
        return view('auth.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->auditLogService->log(
            action: 'auth.logout.web',
            entity: 'User',
            entityId: $userId ?? 0,
            request: $request,
            userId: $userId
        );

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    private function generateAndSendOtp(User $user, ?Request $request = null): void
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

        $this->auditLogService->log(
            action: 'user.otp_sent.web',
            entity: $user,
            request: $request,
            userId: $user->id
        );
    }
}
