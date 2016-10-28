@extends('app')

@section('content')
    <div id="servers-list">
        @include('template.heading', [
            'actions' => [
                [
                    'type' => 'custom',
                    'html' => '
                    <button v-cloak v-on:click="toggleServersGraphOption"
                        :class="(options.showServersGraph ? \'active \' : \'\') + \'btn btn-primary\'">
                        @{{ options.showServersGraph ? \''.trans('server.options.hide_servers_chart').'\' :
                            \''.trans('server.options.show_servers_chart').'\' }}
                    </button>
                    '
                ],
                [
                    'type' => 'custom',
                    'html' => '
                    <button v-cloak v-on:click="toggleExpandedOption"
                        :class="(options.expanded ? \'active \' : \'\') + \'btn btn-default hidden-xs\'"
                        data-toggle="tooltip" title="'.trans('server.options.toggle_expanded_mode').'">
                        <i class="fa fa-arrows-h"></i>
                    </button>
                    '
                ],
                [
                    'condition' => function()
                    {
                        return Gate::check('create', \MineStats\Models\Server::class);
                    },
                    'type' => 'button',
                    'icon' => 'plus',
                    'title' => trans('server.create_server'),
                    'url' => route('serverCreate'),
                ]
            ]
        ])

        <div :class="'container content-margin-top' + (options.expanded ? ' container-expanded' : '')">
            <div class="clearfix" v-cloak>
                <div id="global-graph" class="text-center">
                    <div v-show="options.showServersGraph" class="graph-container"></div>
                </div>
                <div class="filters">
                    <div class="filters-head">
                        <a href="javascript:;" v-on:click="filters.show = !filters.show; saveFilters()">
                            <i :class="['glyphicon', 'glyphicon-chevron-' + (filters.show ? 'up' : 'down') ]"></i>
                            @lang('server.filters.filters')
                            <template v-if="activeFiltersCount > 0">(@{{ activeFiltersCount }})</template>
                        </a>
                        <template v-if="activeFiltersCount > 0">
                            <button class="btn btn-xs btn-default" v-on:click="resetFilters">
                                <i class="fa fa-remove"></i> @lang('server.filters.reset')
                            </button>
                        </template>
                    </div>
                    <div class="filters-content" v-show="filters.show">
                        <?php $cols = 'col-sm-6 col-md-4 col-lg-3'; ?>
                        <form class="clearfix">
                            <div class="{{ $cols }}">
                                <div class="form-group">
                                    <label for="serversFilterTypes">@lang('server.filters.types')</label>
                                    <select id="serversFilterTypes" name="types" v-model="filters.types"
                                            class="form-control" multiple="multiple">
                                        @foreach(\MineStats\Repositories\TypeRepository::getTypes() as $type)
                                            <option value="{{ $type }}">
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="{{ $cols }}">
                                <div class="form-group">
                                    <label for="serversFilterVersions">@lang('server.filters.versions')</label>
                                    <select id="serversFilterVersions" name="versions" v-model="filters.versions"
                                            multiple="multiple">
                                        @foreach(\MineStats\Models\Version::orderBy('protocol_id', 'desc')->get() as $version)
                                            <option value="{{ $version->id }}">
                                                {{ $version->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="{{ $cols }}">
                                <div class="form-group">
                                    <label for="serversFilterLanguages">@lang('server.filters.languages')</label>
                                    <select id="serversFilterLanguages" name="languages" v-model="filters.languages"
                                            class="form-control" multiple="multiple">
                                        @foreach(\MineStats\Models\Language::orderBy('id')->get() as $language)
                                            <option value="{{ $language->id }}">
                                                {{ '<span class="flag flag-'.$language->id.'"></span>' }}
                                                @lang('server.lang.' . $language->id)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="{{ $cols }}">
                                <div class="form-group">
                                    <label class="form-empty-label">&nbsp;</label>
                                    <label class="form-control-static">
                                        <input type="checkbox" name="secondaryLanguages"
                                               v-model="filters.secondaryLanguages">
                                        @lang('server.filters.display_secondary_languages')
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="list">
                    <div v-if="!loaded">
                        <i class="fa fa-spin fa-spinner"></i> @lang('general.loading')
                    </div>
                    <div v-for="(server, index) in orderedServers" :key="server.id" class="case">
                        <div :id="['server-' + server.id]"
                             :class="['server', 'server-order-' + index, 'clearfix', 'popover-container']">
                            @can('update', \MineStats\Models\Server::class)
                                <div class="popover-actions">
                                    <a :href="'{{ route('serverEdit', ['serverId' => '#SERVER_ID#']) }}'.replace('#SERVER_ID#', server.id)"
                                       class="btn btn-default btn-xs">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </div>
                            @endcan
                            <div class="emblem">
                                <img v-if="server.icon" class="icon icon-img" :src="server.icon">
                                <div v-else class="icon icon-empty"></div>
                                <div class="position">#@{{ index + 1 }}</div>
                            </div>
                            <div class="details">
                                <h3>
                                    @{{ server.name }}
                                    <span :class="['type', 'type-' + server.type.toLowerCase()]">@{{ server.type.toLowerCase() }}</span>
                                    <span class="languages">
                                        <template v-for="language in server.languages"
                                                  v-if="filters.secondaryLanguages || language.main">
                                            {{-- TODO(nathan818): vuejs i18n --}}
                                            <span :class="['language', 'language-main', 'flag', 'flag-' + language.id]"
                                                  :title="('server.lang.' + language.id).getLang()"></span>@{{ ' ' }}
                                        </template>
                                    </span>
                                </h3>
                                <div class="address">@{{ server.ip + (server.port ? ':' + server.port : '') }}</div>
                                <ul class="versions">
                                    <li v-for="version in server.versions" class="version">
                                        @{{ version.name }}
                                    </li>
                                </ul>
                                <div class="status status-players" v-if="server.players >= 0">
                                    @lang('server.players')@lang('punctuation.colon') @{{ server.players | number-count }}
                                    <span v-if="server.playersProgress !== undefined" class="progression">
                                    (@{{ server.playersProgress > 0 ?
                                      '+' : '' }}@{{ server.playersProgress | number-count }})
                                </span>
                                </div>
                                <div class="status status-down" v-else>
                                    @lang('server.down')
                                </div>
                            </div>
                            <div class="graph">
                                <div class="graph-container"></div>
                            </div>
                        </div>
                    </div>
                    <template v-if="loaded && orderedServers.length == 0">
                        @lang('server.empty')
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection
