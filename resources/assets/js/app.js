/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example', require('./components/Example.vue'));

// const app = new Vue({
//     el: '#app'
// });

Number.prototype.format = function (n, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
        num = this.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};

/*
 * Vue
 */

Vue.filter('number-count', function (value) {
    return value.format(0, 3, ' ');
});

const serversList = new Vue({
    el: '#servers-list',

    data: {
        servers: [],
        filters: {
            show: false,
            languages: [],
            versions: [],
            secondaryLanguages: false
        },
        _fetchServersTimer: null
    },

    watch: {
        'filters.languages': 'debounceFetchServers',
        'filters.versions': 'debounceFetchServers',
        'filters.secondaryLanguages': 'debounceFetchServers'
    },

    created: function () {
        this.fetchServers();

        // Full reload every 5 minutes
        this._fetchServersTimer = setInterval(function () {
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
    },

    beforeDestroy: function () {
        clearInterval(this._fetchServersTimer);
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
            this.$http.get('/api/servers?' + $.param(options)).then(function (servers) {
                this.servers = servers.body;
            });
        },
        debounceFetchServers: _.debounce(function () {
            this.fetchServers();
        }, 1000)
    },

    computed: {
        orderedServers: function () {
            return _.sortBy(this.servers, function (server) {
                return -server.players;
            });
        }
    }
});
