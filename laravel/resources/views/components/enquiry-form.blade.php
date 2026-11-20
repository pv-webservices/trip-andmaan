{{--
    WhatsApp message composer. It never submits data anywhere: on a valid submit
    it builds a wa.me link and the visitor decides whether to press Send inside
    WhatsApp. Validation lives in resources/js/enquiry.ts.
--}}
@props(['source'])
@php
    $f = $site->global('enquiry_form');
    $directLink = $site->whatsappFor('an Andaman trip (from your '.$source.')');
    $phone = $site->phone();
    $tel = $site->tel();
@endphp
<div class="enquiry-form">
    <h2>{{ $f['heading'] ?? 'Tell us about your dream trip' }}</h2>
    <p>{{ str_replace('{phone}', $phone, $f['intro'] ?? '') }}</p>
    <noscript>
        <p class="form-fallback">
            This form needs JavaScript to prepare your message. You can still
            <a href="{{ $directLink }}" target="_blank" rel="noopener noreferrer">chat with us on WhatsApp</a>
            or <a href="tel:{{ $tel }}">call {{ $phone }}</a>.
        </p>
    </noscript>
    {{-- method="dialog": outside a <dialog> a native submit does nothing, so the
         details can never be sent or put in the URL, even before JS loads. --}}
    <form method="dialog" class="whatsapp-composer needs-js" data-whatsapp="{{ $site->whatsappNumber() }}" data-source="{{ $source }}" novalidate aria-describedby="composer-summary">
        <div id="composer-summary" class="form-summary" role="alert" tabindex="-1" hidden></div>
        <div class="form-grid">
            <div class="field">
                <label for="full-name">Full name</label>
                <input id="full-name" name="fullName" autocomplete="name" required minlength="2" maxlength="100">
                <p class="field-error" id="full-name-error" hidden></p>
            </div>
            <div class="field">
                <label for="mobile">Mobile number <span>(with country code if outside India)</span></label>
                <input id="mobile" name="mobile" type="tel" autocomplete="tel" inputmode="tel" required maxlength="25" placeholder="+91 98765 43210">
                <p class="field-error" id="mobile-error" hidden></p>
            </div>
            <div class="field">
                <label for="travel-date">Travel date <span>(optional)</span></label>
                <input id="travel-date" name="travelDate" type="date">
                <p class="field-error" id="travel-date-error" hidden></p>
            </div>
            <div class="field">
                <label for="travellers">Number of travellers <span>(optional)</span></label>
                <input id="travellers" name="travellers" type="number" min="1" max="100" inputmode="numeric">
                <p class="field-error" id="travellers-error" hidden></p>
            </div>
            <div class="field full">
                <label for="interest">Destination, package or interest <span>(optional)</span></label>
                <input id="interest" name="interest" maxlength="180" placeholder="An island, an experience, a little of everything…">
                <p class="field-error" id="interest-error" hidden></p>
            </div>
            <div class="field full">
                <label for="message">What do you have in mind?</label>
                <textarea id="message" name="message" required minlength="10" maxlength="1000" rows="4" placeholder="Tell us about your group, preferred pace and any places on your wish list."></textarea>
                <p class="field-error" id="message-error" hidden></p>
            </div>
        </div>
        <div class="consent-field">
            <label class="consent">
                <input id="consent" name="consent" type="checkbox" value="agreed" required>
                <span>I understand this opens WhatsApp (operated by Meta) with my details in a message, and nothing is sent until I press Send. Read <a href="/privacy/">privacy &amp; enquiries</a>.</span>
            </label>
            <p class="field-error" id="consent-error" hidden></p>
        </div>
        <button class="btn btn-whatsapp" type="submit"><x-wa-icon :size="20" /> {{ $f['submit_label'] ?? 'Open WhatsApp with my enquiry' }}</button>
        <div class="composer-result" role="status" aria-live="polite">
            <p class="form-status"></p>
            <div class="composer-actions" hidden>
                <a class="btn btn-ghost on-light" data-composer-link href="{{ $directLink }}" target="_blank" rel="noopener noreferrer"><x-wa-icon :size="18" /> Open WhatsApp again</a>
                <a class="btn btn-ghost on-light" href="tel:{{ $tel }}"><x-ta-icon name="phone" :size="18" /> Call {{ $phone }}</a>
            </div>
        </div>
    </form>
    <p class="form-note">
        {{ str_replace('{phone}', $phone, $f['note_before'] ?? 'Prefer to skip the form?') }}
        <a href="{{ $directLink }}" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
        or <a href="tel:{{ $tel }}">call {{ $phone }}</a>. {{ $f['note'] ?? '' }}
    </p>
</div>
