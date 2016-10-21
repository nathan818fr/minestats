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
     * Non-integer id
     */
    public $incrementing = false;

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

    public function toArray()
    {
        $array = parent::toArray();
        if (isset($array['pivot']['main'])) {
            $array['main'] = $array['pivot']['main'];
            unset($array['pivot']);
        }

        return $array;
    }
}