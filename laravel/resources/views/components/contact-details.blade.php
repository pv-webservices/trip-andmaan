<ul class="contact-details">
    <li>
        <span class="contact-icon"><x-ta-icon name="phone" :size="18" /></span>
        <a href="tel:{{ $site->tel() }}">{{ $site->phone() }}</a>
    </li>
    <li>
        <span class="contact-icon wa"><x-wa-icon :size="18" /></span>
        <a href="{{ $site->whatsappFor($site->g('contact', 'default_subject', 'an Andaman trip')) }}" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
    </li>
    <li>
        <span class="contact-icon"><x-ta-icon name="pin" :size="18" /></span>
        <a href="{{ $site->g('contact', 'map_url') }}" target="_blank" rel="noopener noreferrer">{{ $site->g('contact', 'address') }}</a>
    </li>
</ul>
