<?php

namespace MineStats\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int     id
 * @property string  username
 * @property string  password
 * @property boolean must_change_password
 * @property int     role
 */
class User extends Authenticatable
{
    const ACL_OWNER = 3;
    const ACL_ADMIN = 2;
    const ACL_USER = 1;

    const ACL_BY_ID = [
        3 => 'OWNER',
        2 => 'ADMIN',
        1 => 'USER',
    ];

    public static function getAclName($acl)
    {
        if (array_key_exists($acl, self::ACL_BY_ID)) {
            return self::ACL_BY_ID[$acl];
        }

        return null;
    }

    protected $fillable = [
        'id',
        'username',
        'password',
        'must_change_password',
        'acl',
    ];

    /*
     * Serialization hidden fields
     */
    protected $hidden = ['password', 'remember_token'];
}
