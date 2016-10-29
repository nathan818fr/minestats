var ServersGraph;
module.exports = ServersGraph = function (root, getServers, getServerParams) {
    this.init(root, getServers, getServerParams);
};

ServersGraph.prototype = {
    init: function (getRoot, getServers, getServerParams) {
        this.getRoot = getRoot;
        this.getServers = getServers;
        this.getServerParams = getServerParams;
        this.graph = null;
        this.hiddenSeries = {};
        this.cache = null;

        this.loadNavigator();
    },

    reflowContainers: function () {
        if (this.graph)
            this.graph.reflow();
    },

    // - Private part

    loadNavigator: function () {
        Vue.http.get('/api/servers/stats').then(function (res) {
            this.createGraph(res.body.stats);
        }.bind(this));
    },

    createGraph: function (navigatorData) {
        var self = this;
        this.$root = this.getRoot();

        this.graph = Highcharts.StockChart({
            chart: {
                renderTo: this.$root.find('.graph-container')[0],
                type: 'spline',
                animation: Highcharts.svg,
                backgroundColor: null,
                zoomType: 'x'
            },
            title: null,
            navigator: {
                adaptToUpdatedData: false,
                series: {
                    data: navigatorData
                },
                height: 20,
                xAxis: {
                    labels: {
                        formatter: function () {
                            return moment(this.value).format('L');
                        }
                    }
                }
            },
            rangeSelector: {
                buttons: [{
                    type: 'day',
                    count: 1,
                    text: '1d'
                }, {
                    type: 'day',
                    count: 7,
                    text: '7d'
                }, {
                    type: 'month',
                    count: 1,
                    text: '1m'
                }, {
                    type: 'all',
                    text: 'All'
                }],
                selected: 1 // 7 day
            },
            scrollbar: {
                liveRedraw: false
            },
            xAxis: {
                type: 'datetime',
                tickPixelInterval: 250,
                ordinal: false,
                labels: {
                    formatter: function () {
                        return moment(this.value).format('L LT');
                    }
                },
                events: {
                    afterSetExtremes: function (change) {
                        self.updateGraphData(change.min, change.max)
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
            plotOptions: {
                series: {
                    events: {
                        legendItemClick: function (event) {
                            var visible = !event.target.visible;
                            this.hiddenSeries[event.target.name] = !visible;
                        }.bind(this)
                    }
                }
            },
            series: [
                {
                    name: 'Max',
                    data: navigatorData
                }
            ],
            tooltip: {
                useHTML: true,
                formatter: function () {
                    var str = moment(this.x).format('LL LTS') + '<br>';
                    for (var i = 0; i < this.points.length; i++) {
                        var point = this.points[i];
                        str += '<span style="color:' + point.color + ';">●</span> ' +
                            point.series.name + ': <b>' + point.y.format(0, 3, ' ') + '</b>' +
                            '<br>';
                    }
                    return str;
                }
            },
            legend: {
                enabled: true
            },
            exporting: {
                enabled: false
            },
            credits: {
                enabled: true
            }
        });

        this.graph.showLoading('Loading...');
        this.graph.redraw();
        /* this.graph.xAxis[0].setExtremes(
         navigatorData[navigatorData.length - 1][0] - 24 * 60 * 60 * 1000,
         navigatorData[navigatorData.length - 1][0]
         ); */
    },

    updateGraphData: function (from, to) {
        if (this.graph === null)
            return;

        from !== undefined && (this._from = from) || (from = this._from);
        to !== undefined && (this._to = to) || (to = this._to);

        var serversIds = this.getServers();
        if (serversIds.length === 0)
            return;

        if (this.isValidCache(from, to, serversIds))
            this.displayCacheData(from, to, serversIds);
        else
            this.fetchRemoteData(from, to, serversIds);
    },

    isValidCache: function (from, to, serversIds) {
        if (!this.cache || !this.cache.fromDate || !this.cache.toDate || !this.cache.servers)
            return false;
        if (from < this.cache.fromDate)
            return false;
        if (to > this.cache.toDate)
            return false;
        if (!serversIds.every(function (serverId) {
                return this.cache.servers.indexOf(serverId) != -1;
            }.bind(this)))
            return false;

        var minutesInterval = (to - from) / 1000 / 60;
        var config = Config.get('minestats.stats_graph');
        for (var period in config) {
            if (minutesInterval < period) {
                var _config = config[period];
                if (this.cache.statsInterval != _config[0])
                    return false;
                break;
            }
        }

        return true;
    },

    fetchRemoteData: function (from, to, serversIds) {
        this.graph.showLoading('Loading...');
        this.throttledFetchRemoteData(from, to, serversIds);
    },

    throttledFetchRemoteData: _.throttle(function (from, to, serversIds) {
        var params = {
            servers: serversIds.join(','),
            from_date: moment(from).format('YYYY-MM-DD HH:mm:ss'),
            to_date: moment(to).format('YYYY-MM-DD HH:mm:ss')
        };
        Vue.http.get('/api/servers/stats?' + $.param(params)).then(function (res) {
            this.cache = res.body;
            if (this.cache.fromDate)
                this.cache.fromDate = moment.utc(this.cache.fromDate).unix() * 1000;
            if (this.cache.toDate)
                this.cache.toDate = moment.utc(this.cache.toDate).unix() * 1000;
            this.displayCacheData(from, to, serversIds);
            this.graph.hideLoading();
        }.bind(this));
    }, 2500),

    displayCacheData: function (from, to, serversIds) {
        var stats = this.cache.stats;

        // Remove old series (and never remove the first "empty serie")
        while (this.graph.series.length > 1)
            this.graph.series[0].remove(false);

        // Add new series
        var j = 0;
        for (var i in serversIds) {
            var serverId = serversIds[i];
            var stat = stats[serverId];
            if (stat) {
                var serverParams = this.getServerParams(serverId);
                var hidden = this.hiddenSeries[serverParams['name']];
                if (hidden === undefined) {
                    hidden = (j >= 5);
                }
                this.graph.addSeries({
                    name: serverParams['name'],
                    color: serverParams['color'],
                    data: stat,
                    dataGrouping: {
                        enabled: false
                    },
                    visible: !hidden
                }, false);
            }
            j++;
        }

        this.graph.redraw();
    }
};