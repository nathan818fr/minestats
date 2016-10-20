<?php

namespace MineStats\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use MinecraftPinger\MinecraftPinger;
use MinecraftPinger\MinecraftPingException;

/**
 * @property int    id
 * @property string name
 * @property string ip
 * @property int    port
 * @property string type
 * @property mixed  icon
 * @property int    players
 * @property Carbon updated_at
 */
class Server extends Model
{
    protected $fillable = [
        'id',
        'name',
        'ip',
        'port',
        'type',
        'icon',
        'players',
        'updated_at',
    ];

    /*
     * Specify which properties correspond to dates
     */
    protected $dates = ['updated_at'];

    /*
     * Disable Eloquent timestamps
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stats()
    {
        return $this->hasMany('MineStats\Models\ServerStat');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function languages()
    {
        return $this->belongsToMany('MineStats\Models\Language', 'server_languages', null,
            'language')->withPivot('main');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function versions()
    {
        return $this->belongsToMany('MineStats\Models\Version', 'server_versions');
    }

    /**
     * @param array|null $options
     *
     * @throws MinecraftPingException
     */
    public function updatePing(array $options = null)
    {
        $updateIcon = $options['updateIcon'] ?: false;
        $checkVersions = $options['checkVersions'] ?: false;

        $pinger = new MinecraftPinger($this->ip, $this->port);
        $pingResponse = $pinger->ping(2000, 2000);

        // Online players
        $onlinePlayers = isset($pingResponse->players->online) ? $pingResponse->players->online : 0;

        // Favicon
        $favicon = null;
        if ($updateIcon && isset($pingResponse->favicon) && strlen($pingResponse->favicon) < 32767) {
            $favicon = $this->validateBase64Png($pingResponse->favicon);
            if ($favicon === false) {
                $favicon = null;
            }
        }

        // Check versions
        $supportedVersions = null;
        if ($checkVersions) {
            $mainProtocol = isset($pingResponse->version->protocol) ? $pingResponse->version->protocol : null;
            $supportedVersions = [];
            $versions = Version::all();
            foreach ($versions as $version) {
                if ($mainProtocol === $version->protocol_id) {
                    $supportedVersions[$version->id] = $version;
                } else {
                    $checkPingResponse = $pinger->ping(2000, 2000, $version->protocol_id);
                    if (isset($checkPingResponse->version->protocol) &&
                        $checkPingResponse->version->protocol === $version->protocol_id
                    ) {
                        $supportedVersions[$version->id] = $version;
                    }
                    usleep(100 * 1000);
                }
            }
        }

        // Store data
        \DB::transaction(function () use ($onlinePlayers, $favicon, $supportedVersions) {
            // Update basic info
            $this->players = $onlinePlayers;
            if ($favicon !== null) {
                $this->icon = $favicon;
            }
            $this->updated_at = Carbon::now();
            $this->save();

            // Update versions
            if ($supportedVersions !== null) {
                $toRemove = [];
                foreach ($this->versions as $version) {
                    if (!isset($supportedVersions[$version->id])) {
                        $toRemove[] = $version->id;
                    } else {
                        unset($supportedVersions[$version->id]);
                    }
                }
                if (!empty($toRemove)) {
                    $this->versions()->detach($toRemove);
                }
                if (!empty($supportedVersions)) {
                    $this->versions()->attach(array_keys($supportedVersions));
                }
            }

            // Update stats entries
            // TODO: Store stats entries
        });
    }

    protected function validateBase64Png($base64Image)
    {
        $header = 'data:image/png;base64,';
        if (!starts_with($base64Image, $header)) {
            return false;
        }

        $data = substr($base64Image, strlen($header));
        $data = @base64_decode($data, true);
        if ($data === false) {
            return false;
        }

        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($data);
            if ($img === false) {
                return false;
            }
            if (@imagesx($img) !== 64 || @imagesy($img) !== 64) {
                return false;
            }

            ob_start();
            try {
                if (@imagealphablending($img, true) === false ||
                    @imagesavealpha($img, true) === false ||
                    @imagepng($img, null, 9, PNG_ALL_FILTERS) === false
                ) {
                    return false;
                }
                $data = ob_get_contents();
            }
            finally {
                ob_end_clean();
            }
        }

        return $data;
    }
}