@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 bg-green-50 p-3 rounded border border-green-200 mb-4']) }}>
        {{ $status }}
    </div>
@endif