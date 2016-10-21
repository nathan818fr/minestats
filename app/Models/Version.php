<?php

namespace MineStats\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    id
 * @property string type
 * @property int    protocol_id
 * @property string name
 */
class Version extends Model
{
    protected $fillable = [
        'id',
        'type',
        'protocol_id',
        'name',
    ];

    /*
     * Disable Eloquent timestamps
     */
    public $timestamps = false;

    /*
     * Serialization hidden fields
     */
    protected $hidden = ['pivot'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function servers()
    {
        return $this->belongsToMany('MineStats\Models\Server', 'server_versions');
    }
}