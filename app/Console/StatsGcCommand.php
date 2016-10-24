<?php

namespace MineStats\Console;

use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use MineStats\Models\ServerStat;

class StatsGcCommand extends Command
{
    protected $signature = 'tasks:stats-gc';

    public function handle()
    {
        $now = Carbon::now();
        $statsGc = config('minestats.stats_gc');
        ksort($statsGc);

        // Parse config and made groups
        $groups = [];
        $prevElapsed = null;
        $prevMinInterval = null;
        foreach ($statsGc as $elapsed => $minInterval) {
            $elapsed = $elapsed + 0; // Make sure this is an int
            $minInterval = $minInterval + 0; // Make sure this is an int
            if ($elapsed <= 0) {
                $this->error('Elapsed time must be positive!');
                exit(1);
            }
            if ($minInterval <= 0 && $minInterval != -1) {
                $this->error('Elapsed time must be positive or equals to -1!');
                exit(1);
            }
            if ($prevElapsed === null) {
                $groups[] = $this->computeGarbage($now, null, $elapsed, null);
            } else {
                $groups[] = $this->computeGarbage($now, $prevElapsed, $elapsed, $prevMinInterval);
            }
            $prevElapsed = $elapsed;
            $prevMinInterval = $minInterval;
        }
        if ($prevElapsed !== null) {
            $groups[] = $this->computeGarbage($now, $prevElapsed, null, $prevMinInterval);
        }

        // Execute query
        $this->garbage($groups);
    }

    protected function computeGarbage(Carbon $now, $minElapsed, $maxElapsed, $minInterval)
    {
        $fromDate = $maxElapsed !== null ? (clone $now)->subMinutes($maxElapsed) : null;
        $toDate = $minElapsed !== null ? (clone $now)->subMinutes($minElapsed) : null;

        return [
            'toDate'      => $toDate,
            'fromDate'    => $fromDate,
            'minInterval' => $minInterval !== null && $minInterval > 0 ? $minInterval * 60 : null
        ];
    }

    protected function garbage($groups)
    {
        /*
         * For garbage collection, we temporarily create a new table, then we replace the existing table by the new.
         * This method is both simple and faster / less greedy.
         */
        $q = null;
        $qCount = 0;
        foreach ($groups as $group) {
            if ($group['minInterval'] == -1) {
                break;
            }

            $gq = DB::table('server_stats AS t'.($qCount++));
            if ($group['minInterval'] === null) {
                $gq->select(DB::raw('`id`, `server_id`, `recorded_at`, `players`'));
            } else {
                $gq->select(DB::raw('MAX(`id`), `server_id`, '.
                    'FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(`recorded_at`)/'.$group['minInterval'].')*'.$group['minInterval'].') AS recorded_at, '.
                    'MAX(players)'));
            }
            if ($group['fromDate'] !== null) {
                $gq->where('recorded_at', '>=', $group['fromDate']);
            }
            if ($group['toDate'] !== null) {
                $gq->where('recorded_at', '<', $group['toDate']);
            }
            if ($group['minInterval'] !== null) {
                $gq->groupBy(DB::raw('`server_id`, ROUND(UNIX_TIMESTAMP(`recorded_at`)/'.$group['minInterval'].')'));
            }

            if ($q === null) {
                $q = $gq;
            } else {
                $q->unionAll($gq);
            }
        }

        DB::transaction(function () use ($q, $qCount) {
            // Make sure to drop old tables
            DB::unprepared('DROP TABLE IF EXISTS `server_stats_gc`');
            DB::unprepared('DROP TABLE IF EXISTS `server_stats_old`');

            // Create new table
            DB::unprepared('CREATE TABLE `server_stats_gc` LIKE server_stats');

            // Lock and check state
            $lockQuery = 'LOCK TABLES `server_stats_gc` WRITE, `server_stats` WRITE';
            for ($i = 0; $i < $qCount; $i++) {
                $lockQuery .= ', `server_stats` AS t'.$i.' READ';
            }
            DB::unprepared($lockQuery);
            try {
                // Check for concurrent modification (before locking)
                if (!empty(DB::select('SHOW TABLES LIKE \'server_stats_old\''))) {
                    throw new \RuntimeException('Concurrent exception: server_stats_old table exists.');
                }
                if (!empty(DB::select('SELECT 1 FROM `server_stats_gc`'))) {
                    throw new \RuntimeException('Concurrent exception: server_stats_gc is not empty.');
                }

                // Execute garbage and rename tables
                DB::statement('INSERT INTO `server_stats_gc` SELECT * FROM ('.$q->toSql().') AS t', $q->getBindings());
                DB::unprepared('ALTER TABLE `server_stats` RENAME TO `server_stats_old`');
                DB::unprepared('ALTER TABLE `server_stats_gc` RENAME TO `server_stats`');
            }
            finally {
                // Unlock and remove old table
                DB::unprepared('UNLOCK TABLES');
            }
            DB::unprepared('DROP TABLE IF EXISTS `server_stats_old`');
        });
    }
}