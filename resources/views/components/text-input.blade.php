@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'border-gray-300 focus:border-denr-green focus:ring-denr-green rounded-md shadow-sm transition duration-150 ease-in-out'
]) !!}>