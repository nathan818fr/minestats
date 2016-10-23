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
 * Login
 */
Breadcrumbs::register('login', function ($b) {
    $b->parent('serversList');
    $b->push(trans('auth.login'), route('login'));
});