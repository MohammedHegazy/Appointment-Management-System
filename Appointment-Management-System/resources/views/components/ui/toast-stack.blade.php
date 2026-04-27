@php
    $toastMessages = [];

    if (session('success')) {
        $toastMessages[] = ['type' => 'success', 'text' => session('success')];
    }

    foreach ($errors->all() as $message) {
        $toastMessages[] = ['type' => 'error', 'text' => $message];
    }
@endphp

<div id="toast-stack" class="toast-stack" aria-live="polite" aria-atomic="true"></div>
<script>
    window.AppToasts = @json($toastMessages);
</script>
