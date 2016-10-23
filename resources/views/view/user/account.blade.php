@extends('app')

@section('content')
    @include('template.heading', [
        'actions' => [
            [
                'type' => 'button',
                'text' => trans('auth.logout'),
                'url' => \MineStats\Http\Controllers\Web\AuthController::getLogoutUrl()
            ]
        ]
    ])

    <div class="container content-margin-top">
        @if (auth()->user()->must_change_password)
            <div class="panel panel-danger">
                <div class="panel-heading"><strong>@lang('user.you_must_change_password')</strong></div>
                <div class="panel-body">
                    @lang('user.change_password_info')
                </div>
            </div>
        @endif

        <?php
        $col1 = 'col-md-4';
        $col1Off = 'col-md-offset-4';
        $col2 = 'col-md-6';
        ?>
        {!! Form::model(auth()->user(), ['url' => route('account'), 'class' => 'form-horizontal']) !!}
        <div class="form-group">
            {!! Form::label('username', trans('user.username'), ['class' => 'control-label ' . $col1]) !!}
            <div class="{{ $col2 }}">
                {!! Form::text('username', null, ['class' => 'form-control', 'readonly']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('password', trans('user.password'), ['class' => 'control-label ' . $col1]) !!}
            <div class="{{ $col2 }}">
                {!! Form::password('password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('password_confirmation', trans('user.password_confirmation'), ['class' => 'control-label ' . $col1]) !!}
            <div class="{{ $col2 }}">
                {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            <div class="{{ $col1Off }} {{ $col2 }}">
                {!! Form::submit(trans('general.update'), ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@endsection
