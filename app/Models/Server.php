<?php

namespace MineStats\Models;

use Carbon\Carbon;
use ColorThief\ColorThief;
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
 * @property Carbon icon_updated_at
 * @property Carbon versions_updated_at
 * @property int    failed_ping_count
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
        'icon_updated_at',
        'versions_updated_at',
        'failed_ping_count',
        'auto_color',
        'color',
    ];

    /*
     * Specify which properties correspond to dates
     */
    protected $dates = [
        'updated_at',
        'icon_updated_at',
        'versions_updated_at'
    ];

    /*
     * Disable Eloquent timestamps
     */
    public $timestamps = false;

    /*
     * Serialization hidden fields
     */
    protected $hidden = ['icon', 'auto_color'];

    public function toArray()
    {
        $array = parent::toArray();
        if (isset($array['icon']) && !empty($array['icon'])) {
            $array['icon'] = 'data:image/png;base64,'.base64_encode($array['icon']);
        }

        return $array;
    }

    /**
     * @return string
     */
    public function getNameId()
    {
        return 'Server#'.$this->id.'/'.$this->name;
    }

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
        try {
            $pingResponse = $pinger->ping(2000, 2000, 338); // 338 is protocol version of 1.12.1
        } catch (MinecraftPingException $e) {
            $pingResponse = null;
        }

        // Online players
        if ($pingResponse === null) {
            $onlinePlayers = -1;
            $updateIcon = false;
            $checkVersions = false;
        } else {
            $onlinePlayers = (isset($pingResponse->players->online) && $pingResponse->players->online >= 0) ?
                $pingResponse->players->online : 0;
        }

        // Favicon
        $favicon = null;
        $faviconColor = null;
        if ($updateIcon && isset($pingResponse->favicon) && strlen($pingResponse->favicon) < 32767) {
            $res = $this->validateBase64Png($pingResponse->favicon);
            if ($res === false) {
                $favicon = null;
            }
            $favicon = $res['favicon'];
            $faviconColor = $res['color'];
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
                    if ($this->checkVersion($pinger, $version->protocol_id)) {
                        $supportedVersions[$version->id] = $version;
                    }
                    usleep(100 * 1000);
                }
            }
        }

        // Store data
        \DB::transaction(function () use (
            $updateIcon,
            $checkVersions,
            $onlinePlayers,
            $favicon,
            $faviconColor,
            $supportedVersions
        ) {
            $now = Carbon::now();

            // Update basic info
            if ($onlinePlayers == -1) {
                $this->failed_ping_count++;
                if ($this->failed_ping_count >= 3) { // TODO(nathan818): Config value
                    $this->players = $onlinePlayers;
                }
            } else {
                $this->failed_ping_count = 0;
                $this->players = $onlinePlayers;
            }
            if ($favicon !== null) {
                $this->icon = $favicon;
            }
            if ($this->auto_color && $faviconColor !== null) {
                $this->color = $faviconColor;
            }
            $this->updated_at = $now;
            if ($updateIcon) {
                $this->icon_updated_at = $now;
            }
            if ($checkVersions) {
                $this->versions_updated_at = $now;
            }
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
            if ($onlinePlayers != -1 || $this->failed_ping_count > 1) {
                $statEntry = new ServerStat([
                    'recorded_at' => $now,
                    'players'     => $onlinePlayers,
                ]);
                $statEntry->server()->associate($this);
                $statEntry->save();
            }
        });
    }

    protected function checkVersion(MinecraftPinger $pinger, $protocolVersion, $maxRetries = 3)
    {
        $trys = 0;
        do {
            if ($trys > 0) {
                usleep(($trys * 150) * 1000);
            }
            try {
                $checkPingResponse = $pinger->ping(2000, 2000, $protocolVersion);
                if (isset($checkPingResponse->version->protocol) &&
                    $checkPingResponse->version->protocol === $protocolVersion
                ) {
                    return true;
                }
            } catch (MinecraftPingException $ignored) {
            }
            $trys++;
        } while ($trys < $maxRetries);

        return false;
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

            if ($this->auto_color) {
                $palette = ColorThief::getPalette($img, 4, 2);
                $selectedColor = null;
                foreach ($palette as $color) {
                    $colSum = $color[0] + $color[1] + $color[2];
                    if ($colSum > 150 && $colSum < 255 * 3 - 150) {
                        $selectedColor = $color;
                        break;
                    }
                }
                if ($selectedColor !== null) {
                    $r = dechex($selectedColor[0]);
                    $g = dechex($selectedColor[1]);
                    $b = dechex($selectedColor[2]);
                    if (strlen($r) < 2) {
                        $r = '0'.$r;
                    }
                    if (strlen($g) < 2) {
                        $g = '0'.$g;
                    }
                    if (strlen($b) < 2) {
                        $b = '0'.$b;
                    }
                    $faviconColor = $r.$g.$b;
                }
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

        return [
            'favicon' => $data,
            'color'   => isset($faviconColor) ? $faviconColor : null,
        ];
    }
}