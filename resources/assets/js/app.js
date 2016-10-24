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
 * Forms
 */

$('.confirm-submit').removeClass('confirm-submit').click(function (e) {
    if (confirm(Lang.get('general.confirmation_are_you_sure')) !== true)
        e.preventDefault();
});

/*
 * Utils
 */
$(function () {
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body',
        trigger: 'hover'
    });
});

/*
 * Vue
 */

Vue.filter('number-count', function (value) {
    return value.format(0, 3, ' ');
});

if (document.getElementById('servers-list')) {
    require('./app/servers-list.js');
}
