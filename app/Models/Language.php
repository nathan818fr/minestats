<?php

namespace MineStats\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string id
 */
class Language extends Model
{
    protected $fillable = [
        'id',
    ];

    /*
     * Disable Eloquent timestamps
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function servers()
    {
        return $this->belongsToMany('MineStats\Models\Server', 'server_languages', 'language', null)->withPivot('main');
    }
}