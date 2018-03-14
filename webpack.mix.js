let mix = require('laravel-mix');

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

mix.setPublicPath('resources/assets/web');
mix.setResourceRoot('/assets/pc/admin');
mix.sourceMaps(! mix.inProduction());

// More documents see: https://laravel.com/docs/master/mix
// mix.copy('resources/assets/web', 'assets/');
mix.js('resources/assets/admin', 'admin.js');

// 合并并且压缩js插件
mix.scripts([
    'resources/assets/web/js/axios.min.js',
    'resources/assets/web/js/lodash.min.js',
    'resources/assets/web/js/jquery.lazyload.min.js',
    'resources/assets/web/js/jquery.cookie.js',
    'resources/assets/web/js/dexie.min.js',
    'resources/assets/web/js/iconfont.js',
    'resources/assets/web/js/layer.js',
], 'resources/assets/web/global.min.js');

// 合并并且压缩环信js
mix.scripts([
    'resources/assets/web/js/easemob/webim.config.js',
    'resources/assets/web/js/easemob/strophe-1.2.8.min.js',
    'resources/assets/web/js/easemob/websdk-1.4.13.js',
], 'resources/assets/web/easemob.min.js');