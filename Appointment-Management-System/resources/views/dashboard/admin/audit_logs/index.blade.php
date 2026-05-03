@extends('layouts.dashboard')

@section('title', 'Admin Audit Logs')

@section('content')
    <h2 class="dash-title">Audit Logs</h2>

    <x-ui.card class="dash-filter-card" title="Filters">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="filter-grid">
            <x-form.input
                name="search"
                label="Search action/user/entity"
                :value="request('search')"
                placeholder="login_success, user email, User"
            />

            <x-form.select name="action" label="Action">
                <option value="">All actions</option>
                @foreach ($actions as $item)
                    <option value="{{ $item }}" @selected(request('action') === $item)>{{ $item }}</option>
                @endforeach
            </x-form.select>

            <x-form.select name="entity_type" label="Entity">
                <option value="">All entities</option>
                @foreach ($entityTypes as $item)
                    <option value="{{ $item }}" @selected(request('entity_type') === $item)>{{ $item }}</option>
                @endforeach
            </x-form.select>

            <x-form.button type="submit" class="ui-action-btn ui-action-btn--soft">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 18h4v-2h-4v2zm-7-8v2h18v-2H3zm3-6v2h12V4H6z"/></svg>
                <span>Filter</span>
            </x-form.button>
        </form>
    </x-ui.card>

    <x-ui.table
        :headers="['ID', 'When', 'User', 'Action', 'Entity', 'Changes', 'IP']"
        :has-rows="$logs->isNotEmpty()"
        empty="No audit logs yet."
        :colspan="7"
    >
        @foreach ($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td>
                    @if ($log->user)
                        {{ $log->user->first_name }} {{ $log->user->last_name }}<br>
                        <span class="mono">{{ $log->user->email }}</span>
                    @else
                        <span class="mono">system/guest</span>
                    @endif
                </td>
                <td><span class="mono">{{ $log->action }}</span></td>
                <td><span class="mono">{{ $log->entity_type }}#{{ $log->entity_id }}</span></td>
                <td>
                    @if ($log->old_values)
                        <div class="mono">old: {{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}</div>
                    @endif
                    @if ($log->new_values)
                        <div class="mono">new: {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</div>
                    @endif
                </td>
                <td><span class="mono">{{ $log->ip_address }}</span></td>
            </tr>
        @endforeach
    </x-ui.table>

    <div class="page-links">
        {{ $logs->links() }}
    </div>
@endsection
