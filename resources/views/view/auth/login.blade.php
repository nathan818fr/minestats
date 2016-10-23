@extends('app')

@section('content')
    @include('template.heading')

    <div class="container content-margin-top">
        <?php
        $col1 = 'col-md-4';
        $col1Off = 'col-md-offset-4';
        $col2 = 'col-md-6';
        ?>
        {!! Form::open(['url' => route('login'), 'class' => 'form-horizontal']) !!}
        <div class="form-group">
            {!! Form::label('username', trans('user.username'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::text('username', null, ['class' => 'form-control', 'autofocus' => 1]) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('password', trans('user.password'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::password('password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            <div class="{{ $col1Off }} {{ $col2 }}">
                {!! Form::submit(trans('auth.login'), ['class' => 'btn btn-primary btn-block btn-lg']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@endsection