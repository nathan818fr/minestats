<?php
namespace MineStats\Console;

use Illuminate\Console\Command;
use MineStats\Models\Server;

class PingTasksCommand extends Command
{
    protected $signature = 'tasks:ping {duration}';

    public function handle()
    {
        $startTime = time();
        $servers = Server::all();
        \DB::disconnect();

        $duration = $this->argument('duration') + 0;
        $interval = config('minestats.ping_interval');
        $runs = 0;
        $sleep = 1;
        do {
            if ($runs++ > 0) {
                sleep($sleep);
            }
            $sleep = $this->willRun($interval);
            if ($sleep === true) {
                $sleep = 1;
                $this->parallelPing($servers);
            }
        } while (time() + $sleep < $startTime + $duration);
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

    protected function parallelPing($servers)
    {
        $child = false;
        $server = null;
        foreach ($servers as $server) {
            $pid = pcntl_fork();
            if ($pid !== 0) {
                continue;
            }
            $child = true;
            break;
        }
        if (!$child) {
            return;
        }
        unset($servers);

        // Child task

        $this->call('server:ping', [
            '-A'     => true,
            'server' => [$server->id]
        ]);

        exit();
    }
}