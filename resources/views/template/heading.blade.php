{{--
-- Display page heading (with title and breadcrumb)
--
-- Arguments:
--  [page/title] - The current page code name /or/ the title to display
--  [breadcrumb] - The breadcrumb name (by default equals to the page)
--  [breadcrumbArg] - The breadcrumb argument (by default null)
--}}

<?php
if (!isset($breadcrumb)) {
    $breadcrumb = isset($page) ? $page : Route::getCurrentRoute()->getAction()['as'];
}

if (!isset($breadcrumbArg)) {
    $breadcrumbArg = null;
}

if (!isset($title)) {
    $title = Breadcrumbs::get($breadcrumb, $breadcrumbArg)->title;
}
?>

@section('title', $title)

<div class="heading clearfix">
    <div class="container">
        <div class="title">
            <h1>
                @if (isset($prevButton) && $prevButton !== false)
                    <?php
                    if ($prevButton === true) {
                        $_breadcrumbs = Breadcrumbs::generate($breadcrumb, $breadcrumbArg);
                        $backUrl = $_breadcrumbs[count($_breadcrumbs) - 2]->url;
                    } else {
                        $backUrl = $prevButton;
                    }
                    ?>
                    <a href="{{ $backUrl }}" class="btn btn-default" title="@lang('form.back')">
                        <i class="fa fa-angle-left"></i>
                    </a>
                @endif
                {{ $title }}
            </h1>

            @if (!empty($breadcrumb))
                {!! Breadcrumbs::render($breadcrumb, $breadcrumbArg) !!}
            @endif
        </div>

        @if (!empty($actions))
            <div class="actions">
                @foreach($actions as $action)
                    @if (!isset($action['condition']) || $action['condition']())
                        @include('template.components.' . $action['type'], ['action' => $action])
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

@include('template.alerts', [
    'containerClass' => 'container'
])