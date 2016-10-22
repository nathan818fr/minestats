/*
 * All servers graph manager
 */
const AllServersGraph = function (vueServersList) {
    this.init(vueServersList);
};

AllServersGraph.prototype = {
    init: function (vueServersList) {
        this._vueServersList = vueServersList;
    }
};

/*
 * Per-servers realtime graph manager
 */
const ServersRealtimeGraphs = function (vueServersList) {
    this.init(vueServersList);
};

ServersRealtimeGraphs.prototype = {
    /**
     * Initialize realtime graphs. Called on construct.
     *
     * @param vueServersList the servers list vue
     */
    init: function (vueServersList) {
        this._vueServersList = vueServersList;
        this._graphs = {};
        this._pingMaxId = null;
        this._pingTask = null;

        // Bind events
        $(window).resize(this._onWindowResize = _.debounce(function () {
            this.reflowContainers();
        }.bind(this), 200, {maxWait: 1000}));
    },

    /**
     * Reflow containers (when window size change, ...)
     */
    reflowContainers: function () {
        // TODO(nathan818): Config to enable/disable this "same-size" graphs option
        var graph;
        var width;
        var minWidth = null;

        for (var i in this._graphs) {
            graph = this._graphs[i];
            width = $(graph.container).parent().parent().width();
            if (minWidth === null || width < minWidth) {
                minWidth = width;
            }
        }
        for (var i in this._graphs) {
            graph = this._graphs[i];
            var container = $(graph.container).parent();
            width = container.parent().width();
            container.width(minWidth + 'px');
            graph.reflow();
        }
    },

    /**
     * Check for servers update after a vue DOM update
     */
    updateServers: function () {
        var hasNew = false;
        var serverIds = [];

        // Create new graphs
        this._vueServersList.servers.forEach(function (server) {
            serverIds.push(server.id);
            if (!this._graphs[server.id]) {
                hasNew = true;
                this._graphs[server.id] = this.createGraph(server);
            }
        }.bind(this));

        // Remove old graphs
        for (var serverId in this._graphs) {
            if (serverIds.indexOf(parseInt(serverId)) == -1) {
                this.destroyGraph(this._graphs[serverId]);
                delete this._graphs[serverId];
            }
        }

        // Ping (if needed)
        if (hasNew)
            this.ping(); // TODO(nathan818): "Debounce?"
    },

    /**
     * Update the servers data after a vue servers fetch.
     */
    updateData: function () {
        for (var serverId in this._graphs) {
            var graph = this._graphs[serverId];

            var data = graph.series[0].data;
            if (data.length > 0) {
                for (var i in this._vueServersList.servers) {
                    var server = this._vueServersList.servers[i];

                    if (server.id == serverId) {
                        this._vueServersList.$set(server, 'playersProgress', data[data.length - 1].y - data[0].y);
                        break;
                    }
                }
            }
        }
    },

    /**
     * Destroy graphs, unbind events, ...
     */
    destroy: function () {
        // Unbind events
        $(window).unbind('resize', this._onWindowResize);

        this.stopPingTask();
        // TODO: Destroy graphs
    },

    // - Private part

    /**
     * Create a new graph for a server
     * @param server the vue server data
     * @returns the graph
     */
    createGraph: function (server) {
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
                tickPixelInterval: 100,
                labels: {
                    formatter: function () {
                        return moment(this.value).format('LT');
                    }
                }
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
        graph.serieUpdated = 0;
        return graph;
    },

    /**
     * Destroy specified graph.
     * (It is not removed from the list of graphs !)
     *
     * @param graph the graph
     */
    destroyGraph: function (graph) {
        graph.destroy();
        for (var serverId in this._graphs) {
            destroyGraph(this._graphs[serverId]);
            delete graphs[serverId];
        }
    },

    /**
     * Ping the servers (and schedule the next ping if needed)
     */
    ping: function () {
        this.stopPingTask(); // Cancel already scheduled ping task

        // List servers-id to ping
        var servers = [];
        for (var serverId in this._graphs) {
            var graph = this._graphs[serverId];
            if (this._pingMaxId !== null) {
                // TODO(nathan818): first-fill list?
            }
            servers.push(serverId);
        }

        // HTTP request
        var always = function () {
            this._pingTask = setTimeout(function () {
                this.ping();
            }.bind(this), 5500);  // TODO(nathan818): Use value from config
        }.bind(this);

        var params = {
            servers: servers.join(',')
        };
        if (this._pingMaxId !== null)
            params.max_id = this._pingMaxId;
        this._vueServersList.$http.get('/api/servers/stats/realtime?' + $.param(params)).then(function (res) {
            always();

            var data = res.body;
            this._pingMaxId = data.max_id;
            this.updateStats(data.min_date, data.stats);
        }.bind(this), function () {
            always();
            // TODO(nathan818): Notify error
        }.bind(this));
    },

    /**
     * Cancel the programmed ping task (if there is one).
     */
    stopPingTask: function () {
        if (this._pingTask !== null) {
            clearTimeout(this._pingTask);
            this._pingTask = null;
        }
    },

    /**
     * Update graphs from stats list
     *
     * @param minDate minimum date
     * @param stats a stats list
     */
    updateStats: function (minDate, stats) {
        // Add new points
        // var redrawPointsThreshold = 2;
        stats.forEach(function (stat) {
            var graph = this._graphs[stat.server_id];
            if (!graph) {
                console.error('Received ping info for unknown server:' + stat.server_id);
            } else {
                graph.series[0].addPoint({
                    x: moment.utc(stat.recorded_at).unix() * 1000,
                    y: stat.players
                }, false);
                graph.serieUpdated = true;
            }
        }.bind(this));

        // Remove old points & update render
        var minX = moment.utc(minDate).unix() * 1000; // TODO(nathan818): Interval from config
        for (var serverId in this._graphs) {
            var graph = this._graphs[serverId];

            if (graph.serieUpdated) {
                graph.serieUpdated = false;
                var data = graph.series[0].data;

                // Redraw for addPoint
                // We must redraw before removing points to avoid errors with Highchart in console
                graph.redraw();

                // Remove old points
                while (data.length > 0 && data[0].x < minX) {
                    graph.series[0].removePoint(0, false);
                }

                // Update vue
                if (data.length > 0) {
                    for (var i in this._vueServersList.servers) {
                        var server = this._vueServersList.servers[i];
                        if (server.id == serverId) {
                            server.players = data[data.length - 1].y;
                            this._vueServersList.$set(server, 'playersProgress', data[data.length - 1].y - data[0].y);
                            break;
                        }
                    }
                }
            }
        }
    }
};

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
        this.serversRealtimeGraphs = new ServersRealtimeGraphs(this);
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
        this.serversRealtimeGraphs.updateServers();
        this.serversRealtimeGraphs.reflowContainers();
    },

    beforeDestroy: function () {
        this.serversRealtimeGraphs.destroy();
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
                this.serversRealtimeGraphs.updateData();
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