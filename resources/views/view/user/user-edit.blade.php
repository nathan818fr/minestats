@extends('app')

@section('content')
    @include('template.heading', [
        'prevButton' => true,
        'breadcrumbArg' => $user
    ])

    <div class="container content-margin-top">
        <?php
        $col1 = 'col-md-4';
        $col1Off = 'col-md-offset-4';
        $col2 = 'col-md-6';
        ?>
        @if ($user !== null)
            {!! Form::model($user, ['url' => route('userEdit', ['userId' => $user->id]), 'class' => 'form-horizontal']) !!}
        @else
            {!! Form::open(['url' => route('userCreate'), 'class' => 'form-horizontal']) !!}
        @endif
        <div class="form-group">
            {!! Form::label('username', trans('user.username'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::text('username', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('password', trans($user !== null ? 'user.new_password' : 'user.password'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::password('password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            <div class="{{ $col1Off }} {{ $col2 }}">
                <label>
                    {!! Form::checkbox('must_change_password') !!}
                    @lang('user.must_change_password')
                </label>
            </div>
        </div>
        <div class="form-group">
            <?php
            $acls = [];
            foreach (\MineStats\Models\User::ACL_BY_ID as $id => $name) {
                $acls[$id] = trans('user.acl_names.'.$name);
            }
            $acls = array_reverse($acls, true);
            ?>
            {!! Form::label('acl', trans('user.acl'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::select('acl', $acls, null, ['class' => 'form-control']) !!}
                <p class="help-block">
                    @foreach(\MineStats\Models\User::ACL_BY_ID as $id => $name)
                        @lang('user.acl_names.' . $name)@lang('punctuation.colon')
                        @lang('user.acl_helps.' . $name)
                        <br>
                    @endforeach
                </p>
            </div>
        </div>
        <div class="form-group">
            <div class="{{ $col1Off }} {{ $col2 }}">
                {!! Form::submit(trans($user !== null ? 'general.edit' : 'general.create'), ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('usersList') }}" class="btn btn-default">@lang('general.cancel')</a>
                @if ($user !== null)
                    @can('delete', $user)
                        <div class="pull-right">
                            {!! Form::submit(trans('general.delete'), ['class' => 'btn btn-danger confirm-submit', 'name' => 'delete']) !!}
                        </div>
                    @endcan
                @endif
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@endsection