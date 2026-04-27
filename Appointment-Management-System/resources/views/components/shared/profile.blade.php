@props([
    'title',
    'subtitle' => null,
    'avatar' => null,
    'avatarUrl' => null,
    'badges' => [],
])

<section class="profile-hero">
    <div class="profile-hero__left">
        <div class="profile-avatar">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $title }} avatar">
            @else
                {{ $avatar }}
            @endif
        </div>
        <div>
            <h2 class="dash-title">{{ $title }}</h2>
            @if ($subtitle)
                <p class="hint">{{ $subtitle }}</p>
            @endif

            @if (!empty($badges) || isset($badgesSlot))
                <div class="profile-badges">
                    @foreach ($badges as $badge)
                        <span class="status-pill {{ $badge['class'] ?? '' }}">{{ $badge['label'] ?? '' }}</span>
                    @endforeach
                    @isset($badgesSlot)
                        {{ $badgesSlot }}
                    @endisset
                </div>
            @endif
        </div>
    </div>

    @if (trim((string) $slot) !== '')
        <div class="ui-actions">
            {{ $slot }}
        </div>
    @endif
</section>
