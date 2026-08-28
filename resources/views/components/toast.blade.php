@props([
    'type' => 'warning',
    'message' => null,
    'event' => null,
    'dismissMs' => 3000,
])

@php
    $alertClass = match ($type) {
        'success' => 'alert-success',
        'info' => 'alert-info',
        'error' => 'alert-error',
        default => 'alert-warning',
    };
@endphp

@if ($event)
    <div
        x-data="{ message: null }"
        x-on:{{ $event }}.window="message = $event.detail.message; setTimeout(() => message = null, {{ $dismissMs }})"
    >
        <template x-if="message">
            <div class="toast toast-top toast-center z-50 toast-auto-dismiss pointer-events-none">
                <div role="status" @class(['alert shadow-lg', $alertClass])>
                    <span x-text="message"></span>
                </div>
            </div>
        </template>
    </div>
@else
    <div {{ $attributes->class(['toast toast-top toast-center z-50 toast-auto-dismiss pointer-events-none']) }}>
        <div role="status" @class(['alert shadow-lg', $alertClass])>
            @if (filled($message))
                <span>{{ $message }}</span>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
@endif
