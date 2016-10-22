@extends('app')

@section('title', trans('server.servers_list'))
@section('content')
    <div class="container content-margin-top">
        <div id="servers-list" class="clearfix" v-cloak>
            <div class="filters">
                <div class="filters-head">
                    <a href="javascript:;" v-on:click="filters.show = !filters.show; saveFilters()">
                        <i :class="['glyphicon', 'glyphicon-chevron-' + (filters.show ? 'up' : 'down') ]"></i>
                        @lang('server.filters.filters')
                    </a>
                </div>
                <div class="filters-content" v-show="filters.show">
                    <?php $cols = 'col-sm-6 col-md-4 col-lg-3'; ?>
                    <form class=" clearfix">
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
                <div v-for="(server, index) in orderedServers" :key="server.id" class="case">
                    <div :id="['server-' + server.id]"
                         :class="['server', 'server-order-' + index, 'clearfix']">
                        <div class="emblem">
                            <img v-if="server.icon" class="icon icon-img" :src="server.icon">
                            <div v-else class="icon icon-empty"></div>
                            <div class="position">#@{{ index + 1 }}</div>
                        </div>
                        <div class="details">
                            <h3>
                                @{{ server.name }}
                                <span :class="['type', 'type-' + server.type.toLowerCase()]">@{{ server.type.toLowerCase() }}</span>
                                <template v-for="language in server.languages"
                                          v-if="filters.secondaryLanguages || language.main">
                                    <span :class="['language', 'language-main', 'flag', 'flag-' + language.id]"></span>@{{ ' ' }}
                                </template>
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
                                    (@{{ server.playersProgress | number-count }})
                                </span>
                            </div>
                            <div class="status status-down" v-else>
                                @lang('server.down')
                            </div>
                        </div>
                        <div class="graph"><div class="graph-container"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
