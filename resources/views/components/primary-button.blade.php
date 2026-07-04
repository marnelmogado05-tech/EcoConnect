@props(['disabled' => false])

<button {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'inline-flex items-center px-4 py-2 bg-denr-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-denr-light-green focus:bg-denr-light-green active:bg-denr-green focus:outline-none focus:ring-2 focus:ring-denr-green focus:ring-offset-2 transition ease-in-out duration-150'
]) !!}>
    {{ $slot }}
</button>