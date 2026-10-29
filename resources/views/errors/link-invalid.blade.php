{{-- Customer opened an old or edited quotation / report / receipt link (no account, so no buttons) --}}
@include('errors.layout', [
    'code' => 403,
    'title' => 'This link is no longer valid',
    'message' => 'For your security, quotation, report and receipt links now carry a security code. This link is old or incomplete. Please ask us to send you a new link on WhatsApp or email.',
    'signedInCheck' => false,
    'showActions' => false,
])
