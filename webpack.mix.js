const mix = require('laravel-mix');
mix.sass('resources/asset/scss/main.scss', 'public/style.css')
    .js('resources/asset/js/index.js', 'public/index.js')
    .extract()
    .setPublicPath('public')
    // .options({
    //     legacyNodePolyfills: true
    // })

if (mix.inProduction()) {
    mix.version();
    // Set production environment variables
    process.env.MIX_APP_URL2 = 'https://myfamilyplatform.com/';
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