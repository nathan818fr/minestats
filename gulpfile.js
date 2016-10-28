const elixir = require('laravel-elixir');
const shell = require("gulp-shell");
const run = require('gulp-run');

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

gulp.task('refresh_js_localization', shell.task([
    'php artisan js-localization:refresh'
]));

Elixir.extend('cachePath', function (phpPath, output) {
    var paths = new Elixir.GulpPaths().src('public/index.php').output(output);

    var env = process.env;
    env['REQUEST_URI'] = phpPath;

    new Elixir.Task('cachePath', function ($) {
        run('php public/index.php', {env: env, verbosity: 0}).exec()
            .pipe($.rename(paths.output.name))
            .pipe(this.saveAs(gulp));
    }, paths);
});

elixir(function (mix) {
    // Cache js localization & config
    mix.task('refresh_js_localization');
    mix.cachePath('/js-localization/all.js', 'resources/assets/lib/localization.js');

    // Copy libraries files
    mix.copy('resources/assets/lib/flags/flags.png', 'public/assets/css/');
    mix.copy('node_modules/bootstrap-sass/assets/fonts/*', 'public/assets/fonts/');
    mix.copy('node_modules/font-awesome/fonts/', 'public/assets/fonts/');

    // Build css & js
    mix.sass([
        'app.scss',
        '../lib/flags/flags.css',
        '../../../node_modules/bootstrap-multiselect/dist/css/bootstrap-multiselect.css',
        '../../../node_modules/pnotify/src/pnotify.css',
        '../../../node_modules/pnotify/src/pnotify.buttons.css'
    ], 'public/assets/css/app.css').webpack('app.js', 'public/assets/js/app.js');
});
