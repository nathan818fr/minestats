<?php
function rev_asset($path)
{
    $rev = config('minestats.assets_revision');
    if (!empty($rev)) {
        return asset($path).'?rev='.$rev;
    } else {
        return asset($path);
    }
}