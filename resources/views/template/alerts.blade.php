<?php
$hasSession = Request::hasSession();
if ($hasSession || isset($pageErrors)) {
    $alerts = [];
    if ($hasSession && Session::has('flash_notification.message')) {
        $alerts[] = [
                'level'   => Session::get('flash_notification.level'),
                'message' => Session::get('flash_notification.message')
        ];
    }

    if (($hasSession && $errors->any()) || (isset($pageErrors) && !empty($pageErrors))) {
        $errorMessage = '<ul>';
        if ($hasSession)
            foreach ($errors->all() as $error)
                $errorMessage .= '<li>'.$error.'</li>';
        if (isset($pageErrors))
            foreach ($pageErrors as $error)
                $errorMessage .= '<li>'.$error.'</li>';

        $errorMessage .= '</ul>';

        $alerts[] = [
                'level'   => 'danger',
                'message' => $errorMessage
        ];
    }
}
?>

@if (isset($alerts) && !empty($alerts))
    <div class="{{ $containerClass or '' }} alert-container">
        @foreach ($alerts as $alert)
            <div class="{{ $alertClass or '' }} alert alert-{{ $alert['level'] }}">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

                {!! $alert['message'] !!}
            </div>
        @endforeach
    </div>
@endif

<?php
return (isset($alerts) && !empty($alerts));
?>