<?php

namespace MineStats\Console;

use MineStats\Models\Server;

class PingServerCommand extends ServerCommand
{
    protected $signature = 'server:ping {--I|updateIcon} {--C|checkVersions} {server?*}';

    protected $description = 'Ping a server';

    protected $updateIcon = false;

    protected $checkVersions = false;

    public function handle()
    {
        // Parse servers
        $serversArg = $this->argument('server');
        if (empty($serversArg)) {
            $servers = Server::all();
        } else {
            $servers = [];
            foreach ($serversArg as $serverArg) {
                $servers[] = $this->getServer($serverArg);
            }
        }

        // Parse other options
        $this->updateIcon = $this->option('updateIcon');
        $this->checkVersions = $this->option('checkVersions');

        // Ping servers
        foreach ($servers as $server) {
            $this->pingServer($server);
        }
    }

    private function pingServer(Server $server)
    {
        try {
            $server->updatePing([
                'updateIcon'    => $this->updateIcon,
                'checkVersions' => $this->checkVersions,
            ]);
        } catch (\Throwable $e) {
            $this->warn('Error while updating Server#'.$server->id.': '.$e->getMessage());
        }
    }
}