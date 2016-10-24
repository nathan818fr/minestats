<?php
namespace MineStats\Console;

use Illuminate\Console\Command;
use MineStats\Models\Server;
use Mockery\Exception\RuntimeException;

class PingTasksCommand extends Command
{
    protected $signature = 'tasks:ping {duration}';

    protected $pingProcess = []; // process pid by server id

    public function handle()
    {
        declare(ticks = 1);
        pcntl_signal(SIGCHLD, [$this, 'signalHandler'], false);

        // Set defaults variables
        $startTime = time();
        $duration = $this->argument('duration') + 0;
        if (@set_time_limit($duration) === false) {
            $this->warn('Unable to set time limit.');
        }
        $interval = config('minestats.ping_interval');

        // Get servers list and disconnect DB (we will make forks, so the db must be disconnected before)
        $servers = Server::all();
        \DB::disconnect();

        // Start supervisor loop
        $runs = 0;
        $waitDelay = 0;
        do {
            if ($runs++ > 0) {
                sleep($waitDelay);
            }
            $waitDelay = $this->willRun($interval);
            if ($waitDelay === true) {
                $this->launchPings($servers);
            }
        } while (time() + $waitDelay < $startTime + $duration);
    }

    protected function signalHandler($signal)
    {
        while (($pid = pcntl_wait($status, WNOHANG)) > 0) {
            $exitCode = pcntl_wexitstatus($status);
            $serverId = $this->pingProcess[$pid];
            unset($this->pingProcess[$pid]);
            $this->info('Process for Server#'.$serverId.' exited with code '.$exitCode);
        }
    }

    protected function willRun($interval)
    {
        $runFilePath = storage_path('app/cache/run.bin');
        $runFile = fopen($runFilePath, 'c+');
        try {
            flock($runFile, LOCK_EX);
            try {
                $buf = fread($runFile, 8);
                $time = time();
                if (strlen($buf) >= 8) {
                    $lastTime = unpack('J', $buf)[1];
                    if (abs($lastTime - $time) < $interval) {
                        return ($interval - abs($lastTime - $time));
                    }
                }
                fseek($runFile, 0);
                fwrite($runFile, pack('J', $time));
            }
            finally {
                flock($runFile, LOCK_UN);
            }
        }
        finally {
            fclose($runFile);
        }

        return true;
    }

    /*
     * Run
     */
    protected function launchPings($servers)
    {
        foreach ($servers as $server) {
            if (in_array($server->id, $this->pingProcess)) {
                $this->comment('Ping already running for '.$server->getNameId().'.');
                continue;
            }
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new RuntimeException('pcntl_fork error');
            }
            if ($pid !== 0) {
                // We are in main process, continue the loop
                $this->pingProcess[$pid] = $server->id;
                continue;
            }

            // We are in child process, ping the server
            $this->ping($server);

            return;
        }
    }

    protected function ping($server)
    {
        $this->call('server:ping', [
            '-A'     => true,
            'server' => [$server->id]
        ]);
        exit(0);
    }
}