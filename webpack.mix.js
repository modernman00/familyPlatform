const mix = require('laravel-mix');
mix.sass('resources/asset/scss/main.scss', 'public/style.css')
.js('resources/asset/js/index.js', 'public/index.js')
.setPublicPath('public')
.extract();
// .webpackConfig(webpack => {
//     return {
//         output: {
//             publicPath: 'public',
//             chuckFilename: 'jss/[name].js'
//         },
//     };
// });
