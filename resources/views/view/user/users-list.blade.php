@extends('app')

@section('content')
    @include('template.heading', [
        'actions' => [
            [
                    'condition' => function()
                    {
                        return Gate::check('create', \MineStats\Models\User::class);
                    },
                    'type' => 'button',
                    'icon' => 'plus',
                    'title' => trans('user.create_user'),
                    'url' => route('userCreate'),
                ]
        ]
    ])

    <div class="container content-margin-top">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>@lang('user.username')</th>
                <th>@lang('user.acl')</th>
                <th>@lang('user.created_at')</th>
                <th>@lang('user.updated_at')</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ trans('user.acl_names.' . \MineStats\Models\User::getAclName($user->acl)) }}</td>
                    <td>{{ $user->created_at->toDateTimeString() }}</td>
                    <td>{{ $user->updated_at->toDateTimeString() }}</td>
                    <td>
                        @can('update', $user)
                            <a href="{{ route('userEdit', ['userId' => $user->id]) }}" class="btn btn-default btn-xs">
                                <i class="fa fa-edit"></i>
                                @lang('form.edit')
                            </a>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {!! $users->links() !!}
    </div>
@endsection