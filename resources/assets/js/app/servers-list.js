/*
 * Servers list vue
 */
const serversList = new Vue({
    el: '#servers-list',

    data: function () {
        var data = {
            servers: [],
            filters: {
                show: false,
                languages: [],
                versions: [],
                secondaryLanguages: false,
                types: []
            }
        };
        var filters = store.get('minestats.serversList.filters');
        if (filters) {
            _.assign(data.filters, filters);
        }
        return data;
    },

    watch: {
        'filters.languages': 'filtersUpdated',
        'filters.versions': 'filtersUpdated',
        'filters.secondaryLanguages': 'filtersUpdated',
        'filters.types': 'filtersUpdated'
    },

    created: function () {
        this.fetchServers();

        // Full reload every 5 minutes
        this.fetchServersTimer = setInterval(function () {
            this.fetchServers();
        }.bind(this), 5 * 60 * 1000);
    },

    mounted: function () {
        var self = this;
        // TODO(nathan818): Move multi-selects build in mixin
        // TODO(nathan818): i18n for texts
        $('select[name=languages]', this.$el).multiselect({
            nonSelectedText: 'All',
            allSelectedText: 'All',
            selectAllNumber: false,
            enableHTML: true,
            onChange: function () {
                self.filters.languages = this.$select.val();
            }
        });
        $('select[name=versions]', this.$el).multiselect({
            nonSelectedText: 'All',
            allSelectedText: 'All',
            selectAllNumber: false,
            onChange: function () {
                self.filters.versions = this.$select.val();
            }
        });
        $('select[name=types]', this.$el).multiselect({
            nonSelectedText: 'All',
            allSelectedText: 'All',
            selectAllNumber: false,
            onChange: function () {
                self.filters.types = this.$select.val();
            }
        });
    },

    updated: function () {
        serversRealtimeGraph.updateServers();
        serversRealtimeGraph.reflowContainers();
    },

    beforeDestroy: function () {
        clearInterval(this.fetchServersTimer);
    },

    methods: {
        fetchServers: function () {
            var options = {
                with: 'icon,versions,languages',
                secondaryLanguages: this.filters.secondaryLanguages ? '1' : '0'
            };
            if (this.filters.languages.length)
                options.languages = this.filters.languages.join(',');
            if (this.filters.versions.length)
                options.versions = this.filters.versions.join(',');
            if (this.filters.types.length)
                options.types = this.filters.types.join(',');
            this.$http.get('/api/servers?' + $.param(options)).then(function (servers) {
                this.servers = servers.body;
                serversRealtimeGraph.updateData();
            });
        },
        filtersUpdated: _.debounce(function () {
            this.saveFilters();
            this.fetchServers();
        }, 1000),
        saveFilters: function () {
            store.set('minestats.serversList.filters', {
                show: this.filters.show,
                languages: this.filters.languages,
                versions: this.filters.versions,
                secondaryLanguages: this.filters.secondaryLanguages,
                types: this.filters.types
            });
        }
    },

    computed: {
        orderedServers: function () {
            return _.sortBy(this.servers, function (server) {
                return -server.players;
            });
        }
    }
});

/*
 * Per-servers realtime graph manager
 */
var serversRealtimeGraph = function () {
    var graphs = {};

    var createGraph = function (server) {
        var graph = new Highcharts.Chart({
            chart: {
                renderTo: $('#server-' + server.id).find('.graph-container')[0],
                type: 'spline',
                animation: Highcharts.svg,
                spacingLeft: 0,
                spacingBottom: 0,
                backgroundColor: null
            },
            title: null,
            xAxis: {
                type: 'datetime',
                tickPixelInterval: 100
            },
            yAxis: {
                title: {
                    text: null
                },
                floor: 0,
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                useHTML: true,
                formatter: function () {
                    return '<b>' + this.y.format(0, 3, ' ') + '</b><br>' +
                        moment(this.x).format('LTS');
                },
                positioner: function (labelWidth, labelHeight, point) {
                    return {
                        x: point.plotX,
                        y: -labelHeight
                    };
                }
            },
            legend: {
                enabled: false
            },
            exporting: {
                enabled: false
            },
            series: [{
                name: 'Random data',
                marker: {
                    enabled: false
                }
            }],
            credits: {
                enabled: false
            }
        });
        graph.serverId = server.id;
        graph.firstFilled = false;
        return graph;
    };

    var destroyGraph = function (graph) {
        graph.destroy();
    };

    var pingMaxId = null;
    var pingTask = null;
    var ping = function () {
        if (pingTask !== null) {
            clearTimeout(pingTask);
            pingTask = null;
        }

        var servers = [];
        for (var serverId in graphs) {
            var graph = graphs[serverId];
            if (pingMaxId !== null) {
                // TODO(nathan818): first-fill list?
            }
            servers.push(serverId);
        }

        console.log('ping');
        var always = function () {
            pingTask = setTimeout(ping, 5500);  // TODO(nathan818): Use value from config
        };
        var params = {
            servers: servers.join(',')
        };
        if (pingMaxId !== null)
            params.max_id = pingMaxId;
        serversList.$http.get('/api/servers/stats/realtime?' + $.param(params)).then(function (res) {
            always();

            var data = res.body;
            pingMaxId = data.max_id;
            updateStats(data.stats);
        }, function () {
            always();
            // TODO(nathan818): Notify error
        });
    };

    var updateStats = function (stats) {
        // Add new points
        stats.forEach(function (stat) {
            var graph = graphs[stat.server_id];
            if (!graph) {
                console.error('Received ping info for unknown server:' + stat.server_id);
            } else {
                graph.series[0].addPoint({
                    x: moment.utc(stat.recorded_at).unix() * 1000,
                    y: stat.players
                }, false);
                graph.needUpdate = true;
            }
        });
        // Remove old points & update render
        var minTime = moment().subtract(5 * 60, 'seconds').unix() * 1000; // TODO(nathan818): Interval from config
        for (var serverId in graphs) {
            var graph = graphs[serverId];
            if (graph.needUpdate) {
                graph.needUpdate = false;
                var data = graph.series[0].data;
                // Remove old points
                while (data.length > 0 && data[0].y < minTime) {
                    data.shift();
                }
                // Update render
                graph.redraw();
                if (data.length > 0) {
                    for (var i in serversList.servers) {
                        var server = serversList.servers[i];
                        if (server.id == serverId) {
                            server.players = data[data.length - 1].y;
                            serversList.$set(server, 'playersProgress', data[data.length - 1].y - data[0].y);
                            break;
                        }
                    }
                }
            }
        }
    };

    return {
        updateServers: function () {
            var hasNew = false;
            var serverIds = [];

            // Create new graphs
            serversList.servers.forEach(function (server) {
                serverIds.push(server.id);
                if (!graphs[server.id]) {
                    hasNew = true;
                    graphs[server.id] = createGraph(server);
                }
            });

            // Remove old graphs
            for (var serverId in graphs) {
                if (serverIds.indexOf(parseInt(serverId)) == -1) {
                    destroyGraph(graphs[serverId]);
                    delete graphs[serverId];
                }
            }

            if (hasNew)
                ping(); // TODO: "Debounce?"
        },

        updateData: function () {
            for (var serverId in graphs) {
                var graph = graphs[serverId];
                var data = graph.series[0].data;
                if (data.length > 0) {
                    for (var i in serversList.servers) {
                        var server = serversList.servers[i];
                        if (server.id == serverId) {
                            serversList.$set(server, 'playersProgress', data[data.length - 1].y - data[0].y);
                            break;
                        }
                    }
                }
            }
        },

        reflowContainers: function () {
            // TODO(nathan818): Config to enable/disable this "same-size" graphs option
            var graph;
            var width;
            var minWidth = null;
            for (var i in graphs) {
                graph = graphs[i];
                width = $(graph.container).parent().parent().width();
                if (minWidth === null || width < minWidth) {
                    minWidth = width;
                }
            }
            for (var i in graphs) {
                graph = graphs[i];
                var container = $(graph.container).parent();
                width = container.parent().width();
                container.width(minWidth + 'px');
                graph.reflow();
            }
        }
    };
}();

$(window).resize(_.debounce(function () {
    serversRealtimeGraph.reflowContainers();
}, 200, {maxWait: 1000}));