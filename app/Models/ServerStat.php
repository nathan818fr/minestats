<?php

namespace MineStats\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    id
 * @property Carbon recorded_at
 * @property int    players
 */
class ServerStat extends Model
{
    protected $fillable = [
        'id',
        'recorded_at',
        'players',
    ];

    /*
     * Specify which properties correspond to dates
     */
    protected $dates = ['recorded_at'];

    /*
     * Disable Eloquent timestamps
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo('MineStats\Models\Server');
    }
}