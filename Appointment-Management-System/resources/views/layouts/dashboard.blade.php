<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>

<body>
    <x-ui.toast-stack />
    @php
        $sidebarUser = auth()->user();
        $sidebarInitials = strtoupper(substr((string) ($sidebarUser->first_name ?? 'A'), 0, 1) . substr((string) ($sidebarUser->last_name ?? 'D'), 0, 1));
        $roleKey = $sidebarUser?->role?->value ?? 'patient';
        if ($roleKey === 'doctor' && !empty($sidebarUser?->hospital_id)) {
            $roleKey = 'hospital_doctor';
        }

        $roleLabels = config('dashboard.role_labels.' . $roleKey, [
            'welcome' => 'Welcome User',
            'subtitle' => 'Dashboard',
        ]);

        $navItems = collect(config('dashboard.navigation.' . $roleKey, []))
            ->filter(fn (array $item) => \Illuminate\Support\Facades\Route::has((string) ($item['route'] ?? '')))
            ->values();
    @endphp

    <div class="dash-shell-wrap">
        <x-ui.animated-auth-background class="auth-bg--dashboard" />
        <div class="dash-shell">
            <aside class="dash-sidebar">
            <div class="dash-sidebar__brand">
                <div class="dash-logo">
                    @if (!empty($sidebarUser?->avatar))
                        <img src="{{ asset('storage/' . $sidebarUser->avatar) }}" alt="Profile avatar">
                    @else
                        {{ $sidebarInitials }}
                    @endif
                </div>
                <div>
                    <h1>{{ $roleLabels['welcome'] ?? 'Welcome User' }}</h1>
                    <p>{{ $roleLabels['subtitle'] ?? 'Dashboard' }}</p>
                </div>
            </div>

            <nav class="dash-nav">
                @forelse ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                        class="dash-nav__item @if (request()->routeIs(...($item['active'] ?? [$item['route']]))) is-active @endif">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            @switch($item['icon'] ?? 'grid')
                                @case('list')
                                    <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zM7 7h10v2H7V7zm0 4h10v2H7v-2zm0 4h7v2H7v-2z" />
                                    @break
                                @case('bars')
                                    <path d="M4 22h16v-2H4v2zM6 9h3v9H6V9zm4-6h4v15h-4V3zm5 8h3v7h-3v-7z"/>
                                    @break
                                @case('doctor')
                                    <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0 6a3 3 0 1 1 0 6 3 3 0 0 1 0-6zm0 10c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08s5.97 1.09 6 3.08A7.18 7.18 0 0 1 12 19z"/>
                                    @break
                                @case('users')
                                    <path d="M16 11c1.66 0 2.99-1.79 2.99-4S17.66 3 16 3s-3 1.79-3 4 1.34 4 3 4zm-8 0c1.66 0 2.99-1.79 2.99-4S9.66 3 8 3 5 4.79 5 7s1.34 4 3 4zm0 2c-2.33 0-7 1.17-7 3.5V21h14v-4.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.98 1.97 3.45V21h7v-4.5c0-2.33-4.67-3.5-7-3.5z"/>
                                    @break
                                @case('settings')
                                    <path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.07-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.14 7.14 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.58.22-1.12.53-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.65 8.84a.5.5 0 0 0 .12.64l2.03 1.58c-.05.31-.08.63-.08.94s.03.63.08.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.5.41 1.05.73 1.63.95l.36 2.53a.5.5 0 0 0 .5.42h3.84a.5.5 0 0 0 .5-.42l.36-2.53c.58-.22 1.13-.54 1.63-.95l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.02-1.58zM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5z"/>
                                    @break
                                @default
                                    <path d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-18v6h8V3h-8z" />
                            @endswitch
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @empty
                    <a href="{{ route('home') }}" class="dash-nav__item @if (request()->routeIs('home')) is-active @endif">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 13h8V3H3v10zm10 8h8V11h-8v10zM3 21h8v-6H3v6zm10-18v6h8V3h-8z" />
                        </svg>
                        <span>Home</span>
                    </a>
                @endforelse
            </nav>

            <form action="{{ route('logout') }}" method="POST" class="dash-sidebar__footer">
                @csrf
                <button type="submit" class="dash-logout-btn">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            d="M10 17l1.41-1.41L8.83 13H20v-2H8.83l2.58-2.59L10 7l-5 5 5 5zm-6 4h8v-2H4V5h8V3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z" />
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </aside>

            <div class="dash-main">
                <x-ui.animated-auth-background class="auth-bg--dashboard auth-bg--dashboard-main" />
                <header class="dash-topbar">
                <h2 class="dash-brand">@yield('title', 'Dashboard')</h2>
                <div class="dash-user-chip">
                    <div class="dash-user-chip__avatar">
                        @if (!empty($sidebarUser?->avatar))
                            <img src="{{ asset('storage/' . $sidebarUser->avatar) }}" alt="Profile avatar">
                        @else
                            {{ $sidebarInitials }}
                        @endif
                    </div>
                    <p class="dash-topbar__meta">Signed in as {{ $sidebarUser->first_name ?? 'User' }}</p>
                </div>
            </header>

                <main class="dash-container">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/toast.js') }}" defer></script>
    <script src="{{ asset('js/file-upload.js') }}" defer></script>
    @stack('scripts')
</body>

</html>
