@extends('app')

@section('title', trans('server.servers_list'))
@section('content')
    <div class="container content-margin-top">
        <div id="servers-list" class="clearfix" v-cloak>
            <div class="filters">
                <select name="languages" v-model="filters.languages" multiple="multiple">
                    @foreach(\MineStats\Models\Language::orderBy('id')->get() as $language)
                        <option value="{{ $language->id }}">
                            @lang('server.lang.' . $language->id)
                        </option>
                    @endforeach
                </select>
                <select name="versions" v-model="filters.versions" multiple="multiple">
                    @foreach(\MineStats\Models\Version::orderBy('protocol_id', 'desc')->get() as $version)
                        <option value="{{ $version->id }}">
                            {{ $version->name }}
                        </option>
                    @endforeach
                </select>
                <label>
                    <input type="checkbox" name="secondaryLanguages" v-model="filters.secondaryLanguages">
                    @lang('server.filters.display_secondary_languages')
                </label>
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
                            </div>
                            <div class="status status-down" v-else>
                                @lang('server.down')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
