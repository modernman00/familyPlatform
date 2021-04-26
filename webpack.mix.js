const mix = require('laravel-mix');
mix.sass('resources/asset/scss/main.scss', 'public/style.css')
.js('resources/asset/js/index.js', 'public/index.js')
.extract()
.setPublicPath('public')

if(mix.inProduction()){
    mix.version();
}

mix.babelConfig({
    plugins: ['@babel/plugin-syntax-dynamic-import']
})
// // .version();
// .webpackConfig(webpack => {
//     return {
//         output: {
//             // publicPath: 'public',
//             chuckFilename: 'codeSplit/[name].js'
//         },
//     };
// });
