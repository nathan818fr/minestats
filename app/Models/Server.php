<?php

namespace MineStats\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
}