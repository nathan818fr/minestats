<?php

namespace MineStats\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use MineStats\Http\Controllers\Controller;
use MineStats\Models\Language;
use MineStats\Models\Server;
use MineStats\Models\ServerStat;
use MineStats\Models\Version;
use MineStats\Repositories\TypeRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServerController extends Controller
{
    protected function languagesList()
    {
        $list = '';
        foreach (Language::select('id')->get() as $language) {
            if ($list != '') {
                $list .= ',';
            }
            $list .= $language->id;
        }

        return $list;
    }

    protected function versionsList()
    {
        $list = '';
        foreach (Version::select('id')->get() as $version) {
            if ($list != '') {
                $list .= ',';
            }
            $list .= $version->id;
        }

        return $list;
    }

    public function getServers(Request $req)
    {
        // TODO(nathan818): Cache servers lists requests

        // Validate request
        $this->arrayParam($req, 'with');
        $this->arrayParam($req, 'languages');
        $this->arrayParam($req, 'versions');
        $this->arrayParam($req, 'types');
        $this->validateOnly($req, [
            'with'               => 'array|filled|in:icon,versions,languages',
            'order'              => 'order_in:id,players',
            'languages'          => function () {
                return 'array|filled|in:'.$this->languagesList();
            },
            'secondaryLanguages' => 'boolean',
            'versions'           => function () {
                return 'array|filled|in:'.$this->versionsList();
            },
            'types'              => 'array|filled|in:'.join(',', TypeRepository::getTypes()),
        ]);

        $with = $req->get('with');
        $order = $this->parseOrder($req->get('order'));
        $languages = $req->get('languages');
        $secondaryLanguages = $req->get('secondaryLanguages') ? true : false;
        $versions = $req->get('versions');
        $types = $req->get('types');

        // Get servers
        $servers = Server::query();

        if ($order !== null) {
            $servers->orderBy($order[0], $order[1]);
        }

        if ($types !== null) {
            $servers->whereIn('type', $types);
        }

        if ($with !== null && in_array('versions', $with)) {
            $servers->with([
                'versions' => function ($q) {
                    $q->orderBy('protocol_id', 'DESC');
                }
            ]);
        }
        if (!empty($versions)) {
            $servers->whereHas('versions', function ($q) use ($versions) {
                $q->whereIn('versions.id', $versions);
            });
        }

        if ($with !== null && in_array('languages', $with)) {
            $servers->with([
                'languages' => function ($q) {
                    $q->orderBy('main', 'DESC');
                }
            ]);
        }
        if (!empty($languages)) {
            $servers->whereHas('languages', function ($q) use ($languages, $secondaryLanguages) {
                $q->whereIn('languages.id', $languages);
                if (!$secondaryLanguages) {
                    $q->where('main', true);
                }
            });
        }

        $servers = $servers->get();

        if ($with !== null && in_array('icon', $with)) {
            $servers->makeVisible('icon');
        }

        return response()->json($servers);
    }

    public function getRealtimeServersStats(Request $req)
    {
        $this->arrayParam($req, 'servers');
        $this->arrayParam($req, 'new_servers');
        $this->validateOnly($req, [
            'servers'     => 'numeric_array',
            'new_servers' => 'numeric_array',
            'max_id'      => 'integer',
        ]);

        $servers = $req->get('servers');
        $newServers = $req->get('new_servers');

        if (empty($servers) && empty($newServers)) {
            throw new BadRequestHttpException('At least on of servers or new_servers must be provided!');
        }

        $maxId = $req->get('max_id');
        $oldDate = Carbon::now()->subSeconds(config('minestats.ui_realtime_period'));

        if ($maxId === null && !empty($newServers)) {
            throw new BadRequestHttpException('new_servers present without max_id!');
        }

        $stats = ServerStat::query();
        $stats->where(function (Builder $grp) use ($stats, $maxId, $oldDate, $servers, $newServers) {
            if (!empty($newServers)) {
                $grp->where(function (Builder $q) use ($oldDate, $newServers) {
                    $q->whereIn('server_id', $newServers);
                });
            }
            if (!empty($servers)) {
                $grp->orWhere(function (Builder $q) use ($maxId, $oldDate, $servers) {
                    if ($maxId !== null) {
                        $q->where('id', '>', $maxId);
                    }
                    $q->whereIn('server_id', $servers);
                });
            }
        });
        $stats->where('recorded_at', '>=', $oldDate);
        $stats->orderBy('id', 'asc');

        // Return result
        $stats = $stats->get();
        if (($statsCount = count($stats)) != 0) {
            $maxId = $stats[$statsCount - 1]->id;
        }

        return response()->json([
            'max_id'   => $maxId,
            'min_date' => $oldDate->toDateTimeString(),
            'stats'    => $stats
        ]);
    }
}