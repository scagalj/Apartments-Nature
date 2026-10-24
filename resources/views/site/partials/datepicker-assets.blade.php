{{-- flatpickr date picker (no jQuery) with the page language --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" defer></script>
@if(app()->getLocale() !== 'en')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/{{ app()->getLocale() }}.min.js" defer></script>
@endif
