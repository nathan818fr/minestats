@if ($breadcrumbs && count($breadcrumbs) > 1)
    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($breadcrumb->url && !$breadcrumb->last)
                <li>
                    <a href="{{ $breadcrumb->url }}">
                        {!! e($breadcrumb->title) !!}
                    </a>
                </li>
            @else
                <li class="active">{{ $breadcrumb->title }}</li>
            @endif
        @endforeach
    </ol>
@endif