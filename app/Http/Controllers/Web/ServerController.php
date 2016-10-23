<?php

namespace MineStats\Http\Controllers\Web;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use MineStats\Http\Controllers\Controller;
use MineStats\Models\Server;
use MineStats\Repositories\TypeRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,MineStats\Models\Server')->only(['getServerCreate', 'postServerCreate']);
        $this->middleware('can:update,MineStats\Models\Server')->only(['getServerEdit', 'postServerEdit']);
    }

    public function getServersList()
    {
        return view('view.server.servers-list');
    }

    public function getServerCreate()
    {
        return $this->getServerEdit(null);
    }

    public function postServerCreate(Request $req)
    {
        return $this->postServerEdit($req, null);
    }

    public function getServerEdit($serverId)
    {
        if ($serverId === null) {
            $server = null;
        } else {
            $server = Server::findOrFail($serverId);
        }

        return view('view.server.server-edit', [
            'server' => $server
        ]);
    }

    public function postServerEdit(Request $req, $serverId)
    {
        if ($req->has('delete') && $serverId !== null) {
            return $this->deleteServer($serverId);
        }

        $this->validate($req, [
            'name'      => 'required|max:30',
            'ip'        => 'required|host',
            'port'      => 'integer|min:1|max:65536',
            'type'      => 'required|in:'.join(',', TypeRepository::getTypes()),
            'languages' => 'array'
        ]);

        return \DB::transaction(function () use ($serverId, $req) {
            if ($serverId === null) {
                $server = new Server([
                    'players'           => 0,
                    'failed_ping_count' => 0
                ]);
            } else {
                $server = Server::where('id', $serverId)->with('languages')->lockForUpdate()->first();
                if ($server === null) {
                    throw new ModelNotFoundException();
                }
            }

            $server->name = $req->get('name');
            $server->ip = $req->get('ip');
            $server->port = $req->has('port') ? $req->get('port') : null;
            $server->type = $req->get('type');

            $server->save();

            $languages = $req->get('languages');
            foreach ($languages as $languagesId => $value) {
                if (strtolower($languagesId) !== $languagesId) {
                    throw new BadRequestHttpException();
                }
            }

            foreach ($server->languages as $language) {
                if (isset($languages[$language->id])) {
                    $value = $languages[$language->id];
                    unset($languages[$language->id]);
                    if ($value == '') {
                        $server->languages()->detach($language->id);
                    } else {
                        $main = ($value == '1');
                        if ($language->pivot->main != $main) {
                            $language->pivot->main = $main;
                            $language->pivot->save();
                        }
                    }
                }
            }

            foreach ($languages as $languageId => $value) {
                if ($value == '') {
                    continue;
                }
                $server->languages()->attach($languageId, [ // Integrity constraint violation on invalid id
                    'main' => ($value == '1')
                ]);
            }

            if ($serverId === null) {
                \Flash::success(trans('server.server_created'));
            } else {
                \Flash::success(trans('server.server_edited'));
            }

            return redirect(route('serversList'));
        });
    }

    public function deleteServer($serverId)
    {
        \Gate::authorize('delete', Server::class);

        $server = Server::findOrFail($serverId);
        $server->delete();
        \Flash::error(trans('server.server_deleted'));

        return redirect(route('serversList'));
    }
}