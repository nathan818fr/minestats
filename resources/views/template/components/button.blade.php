<?php
$arr = get_defined_vars();
$elem = !empty($action['url']) ? 'a' : 'button';
?>
<{{ $elem }}
        class="btn btn-default"
{!! !empty($action['url']) ? 'href="'.e($action['url']).'"' : '' !!}
{!! !empty($action['title']) ? 'title="'.e($action['title']).'"' : '' !!}
>
@if (isset($action['icon']))
    <i class="fa fa-{{ $action['icon'] }}"></i>
@endif
{{ $action['text'] or '' }}
</{{ $elem }}>