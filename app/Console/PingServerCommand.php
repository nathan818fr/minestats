<?php

namespace MineStats\Console;

use Carbon\Carbon;
use MineStats\Models\Server;

class PingServerCommand extends ServerCommand
{
    protected $signature = 'server:ping {--I|updateIcon} {--C|checkVersions} {--A|auto} {server?*}';

    protected $description = 'Ping a server';

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
        $autoUpdate = $this->option('auto');
        $updateIcon = $this->option('updateIcon');
        $checkVersions = $this->option('checkVersions');

        // Ping servers
        foreach ($servers as $server) {
            $options = [
                'updateIcon'    => $updateIcon,
                'checkVersions' => $checkVersions,
            ];
            if ($autoUpdate) {
                if ($server->icon_updated_at->diffInMinutes(Carbon::now()) >
                    config('minestats.favicon_cache_period')
                ) {
                    $options['updateIcon'] = true;
                }
                if ($server->versions_updated_at->diffInMinutes(Carbon::now()) >
                    config('minestats.versions_cache_period')
                ) {
                    $options['checkVersions'] = true;
                }
            }
            $this->pingServer($server, $options);
        }
    }

    private function pingServer(Server $server, $options)
    {
        $this->comment('Updating '.$server->getNameId().'...');
        try {
            $server->updatePing($options);
            $this->info($server->getNameId().' updated!');
        } catch (\Throwable $e) {
            $this->warn('Error while updating '.$server->getNameId().': '.$e->getMessage());
        }
    }
}