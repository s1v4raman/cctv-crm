@include('errors.layout', [
    'code' => 500,
    'title' => 'Something went wrong on our side',
    'message' => 'The problem has been logged. Please try again in a minute. If it keeps happening, tell the admin what you were doing.',
    'signedInCheck' => false,
])
