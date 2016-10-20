<?php

namespace MineStats\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MineStats\Models\Server;

abstract class ServerCommand extends Command
{
    /**
     * @param string $serverArg
     *
     * @return Server
     */
    public function getServer($serverArg)
    {
        if (starts_with($serverArg, 'name:')) {
            $server = Server::where('name', substr($serverArg, 5))->first();
        } else {
            $server = Server::find($serverArg);
        }

        if ($server === null) {
            throw (new ModelNotFoundException())->setModel(Server::class);
        }

        return $server;
    }
}