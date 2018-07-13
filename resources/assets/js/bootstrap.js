window._ = require('lodash');
window.store = require('store');

/**
 * Lang
 */
require('../lib/localization');
Lang.setLocale(Laravel.locale);

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');
require('bootstrap-multiselect');

/**
 * Vue is a modern JavaScript library for building interactive web interfaces
 * using reactive data binding and reusable components. Vue's API is clean
 * and simple, leaving you to focus on building your next great project.
 */

window.Vue = require('../../../node_modules/vue/dist/vue.min.js');
require('vue-resource');

/**
 * Highchart
 */
window.Highcharts = require('highcharts/highstock');

/**
 * Moment
 */
window.moment = require('moment');

/**
 * PNotify
 */
window.PNotify = require('pnotify');
require('pnotify/src/pnotify.buttons.js');
PNotify.prototype.options.styling = 'fontawesome';
PNotify.prototype.options.delay = 4000;
PNotify.prototype.options.addclass = 'stack-bottomright';
PNotify.prototype.options.stack = {'dir1': 'up', 'dir2': 'left', 'push': 'top', 'firstpos1': 5, 'firstpos2': 5};
PNotify.prototype.options.buttons.sticker = false;

/**
 * We'll register a HTTP interceptor to attach the "CSRF" header to each of
 * the outgoing requests issued by this application. The CSRF middleware
 * included with Laravel will automatically verify the header's value.
 */

Vue.http.interceptors.push(function (request, next) {
    request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);

    next();
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from "laravel-echo"

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'your-pusher-key'
// });
