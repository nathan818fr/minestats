const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.copy('resources/assets/lib/flags/flags.png', 'public/assets/css/');
    mix.copy('node_modules/bootstrap-sass/assets/fonts/*', 'public/assets/fonts/');
    mix.copy('node_modules/font-awesome/fonts/', 'public/assets/fonts/');
    mix.sass([
        'app.scss',
        '../lib/flags/flags.css',
        '../../../node_modules/bootstrap-multiselect/dist/css/bootstrap-multiselect.css'
    ], 'public/assets/css/app.css').webpack('app.js', 'public/assets/js/app.js');
});
