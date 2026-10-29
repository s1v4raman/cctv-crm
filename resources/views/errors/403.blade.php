@php
    $reason = isset($exception) ? trim($exception->getMessage()) : '';
    $generic = ['', 'Forbidden', 'Unauthorized action.', 'This action is unauthorized.'];
@endphp
@include('errors.layout', [
    'code' => 403,
    'title' => "You don't have access to this page",
    'message' => 'Your account cannot open this page or do this action. If you need it, ask an admin to change your access.',
    'detail' => in_array($reason, $generic, true) ? null : $reason,
])
