<?php

namespace MineStats\Http\Controllers\Api;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use MineStats\Http\Controllers\Controller;
use MineStats\Models\Language;
use MineStats\Models\Server;
use MineStats\Models\Version;

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
        $this->validateOnly($req, [
            'with'               => 'array|filled|in:icon,versions,languages',
            'order'              => 'order_in:id,players',
            'languages'          => 'array|filled|in:'.$this->languagesList(),
            'secondaryLanguages' => 'boolean',
            'versions'           => 'array|filled|in:'.$this->versionsList(),
        ]);

        $with = $req->get('with');
        $order = $this->parseOrder($req->get('order'));
        $languages = $req->get('languages');
        $secondaryLanguages = $req->get('secondaryLanguages') ? true : false;
        $versions = $req->get('versions');

        // Get servers
        $servers = Server::query();

        if ($order !== null) {
            $servers->orderBy($order[0], $order[1]);
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
}