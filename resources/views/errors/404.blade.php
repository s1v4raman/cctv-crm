@include('errors.layout', [
    'code' => 404,
    'title' => 'Page not found',
    'message' => 'The page may have been moved or deleted, or the link is not complete.',
])
