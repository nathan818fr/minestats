@extends('app')

@section('content')
    @include('template.heading', [
        'prevButton' => true,
        'breadcrumbArg' => $server
    ])

    <div class="container content-margin-top">
        <?php
        $col1 = 'col-md-4';
        $col1Off = 'col-md-offset-4';
        $col2 = 'col-md-6';
        ?>
        @if ($server !== null)
            {!! Form::model($server, ['url' => route('serverEdit', ['serverId' => $server->id]), 'class' => 'form-horizontal']) !!}
        @else
            {!! Form::open(['url' => route('serverCreate'), 'class' => 'form-horizontal']) !!}
        @endif
        <div class="form-group">
            {!! Form::label('name', trans('server.server_name'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('ip', trans('server.server_address'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::text('ip', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('port', trans('server.server_port'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::number('port', null, ['class' => 'form-control', 'placeholder' => '25565', 'min' => 1, 'max' => 65536]) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('type', trans('server.server_type'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                {!! Form::select('type', array_combine(\MineStats\Repositories\TypeRepository::getTypes(),
                    \MineStats\Repositories\TypeRepository::getTypes()), null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('languages', trans('server.server_languages'), ['class' => $col1 . ' control-label']) !!}
            <div class="{{ $col2 }}">
                <table class="table">
                    <?php
                    $selectedLanguages = [];
                    if ($server !== null)
                        foreach ($server->languages as $language) {
                            $selectedLanguages[$language->id] = ($language->pivot->main) ? '1' : '0';
                        }
                    ?>
                    @foreach(\MineStats\Models\Language::all() as $language)
                        <tr>
                            <td>
                                <i class="flag flag-{{ $language->id }}"></i>
                                @lang('server.lang.' . $language->id)
                            </td>
                            <td>
                                <label>
                                    {!! Form::radio('languages['.$language->id.']', '1', ($selectedLanguages[$language->id] ?? null) === '1') !!}
                                    @lang('server.main_language')
                                </label>
                            </td>
                            <td>
                                <label>
                                    {!! Form::radio('languages['.$language->id.']', '0', ($selectedLanguages[$language->id] ?? null) === '0') !!}
                                    @lang('server.secondary_language')
                                </label>
                            </td>
                            <td>
                                <label>
                                    {!! Form::radio('languages['.$language->id.']', '', ($selectedLanguages[$language->id] ?? null) === null) !!}
                                    @lang('server.not_language')
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <div class="form-group">
            <div class="{{ $col1Off }} {{ $col2 }}">
                {!! Form::submit(trans($server !== null ? 'form.edit' : 'form.create'), ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('serversList') }}" class="btn btn-default">@lang('form.cancel')</a>
                @if ($server !== null)
                    <div class="pull-right">
                        {!! Form::submit(trans('form.delete'), ['class' => 'btn btn-danger confirm-submit', 'name' => 'delete']) !!}
                    </div>
                @endif
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@endsection