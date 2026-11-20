{{-- Direct link to the WhatsApp chat with a context-specific message (no JavaScript needed). --}}
@props(['subject', 'class' => 'btn btn-whatsapp'])
<a class="{{ $class }}" href="{{ $site->whatsappFor($subject) }}" target="_blank" rel="noopener noreferrer"><x-wa-icon :size="18" />{{ $slot }}</a>
