<?php
$elem = !empty($url) ? 'a' : 'button';
?>
<{{ $elem }}
        class="btn btn-default"
{!! !empty($url) ? 'href="'.e($url).'"' : '' !!}
{!! !empty($title) ? 'title="'.e($title).'"' : '' !!}
>
<i class="fa fa-{{ $icon }}"></i>{{ $text or '' }}
</{{ $elem }}>