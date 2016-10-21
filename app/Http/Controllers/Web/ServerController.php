<?php

namespace MineStats\Http\Controllers\Web;

use MineStats\Http\Controllers\Controller;

class ServerController extends Controller
{
    public function getServersList()
    {
        return view('view.server.servers-list');
    }
}