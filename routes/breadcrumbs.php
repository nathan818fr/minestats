<?php

/*
 * Server
 */
Breadcrumbs::register('serversList', function ($b) {
    $b->push(trans('server.servers_list'), route('serversList'));
});

Breadcrumbs::register('serverCreate', function ($b) {
    $b->parent('serversList');
    $b->push(trans('server.create_server'), route('serverCreate'));
});

Breadcrumbs::register('serverEdit', function ($b, $server) {
    $b->parent('serversList');
    $b->push(trans('server.edit_server', [
        'serverName' => $server->name
    ]), route('serverEdit', [
        'serverId' => $server->id
    ]));
});

/*
 * User
 */

Breadcrumbs::register('usersList', function ($b) {
    $b->push(trans('user.users_list'), route('usersList'));
});

Breadcrumbs::register('userCreate', function ($b) {
    $b->parent('usersList');
    $b->push(trans('user.create_user'), route('userCreate'));
});

Breadcrumbs::register('userEdit', function ($b, $user) {
    $b->parent('usersList');
    $b->push(trans('user.edit_user', ['username' => $user->username]), route('userEdit', [
        'userId' => $user->id
    ]));
});

Breadcrumbs::register('account', function ($b) {
    $b->push(trans('user.my_account'), route('account'));
});

/*
 * Login
 */
Breadcrumbs::register('login', function ($b) {
    $b->push(trans('auth.login'), route('login'));
});