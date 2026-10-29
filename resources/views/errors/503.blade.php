@include('errors.layout', [
    'code' => 503,
    'title' => 'Down for a short update',
    'message' => 'The CRM is being updated. Please try again in a few minutes.',
    'signedInCheck' => false,
])
