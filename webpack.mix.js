var mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// main style sheet
mix.styles('resources/assets/css/global.css', 'public/css/global.css');
// combine d3 & nvd3 as are always used together
mix.js(['resources/assets/js/packages/d3.js',
    'resources/assets/js/packages/nv.d3.js'], 'public/js/graphing.js');
// combine packages for editLog page
mix.js(['resources/assets/js/packages/codemirror/codemirror.js',
    'resources/assets/js/packages/codemirror/overlay.js',
    'resources/assets/js/packages/codemirror/show-hint.js',
    'resources/assets/js/packages/codemirror/runmode.js',
    'resources/assets/js/log.edit.js'], 'public/js/log.edit.js');
// combine comments
mix.js(['resources/assets/js/packages/jCollapsible.js',
    'resources/assets/js/comments.js'], 'public/js/comments.js');

if (mix.inProduction()) {
    mix.version();
}