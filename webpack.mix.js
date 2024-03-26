const mix = require('laravel-mix');
mix.sass('resources/asset/scss/main.scss', 'public/style.css')
    .js('resources/asset/js/index.js', 'public/index.js')
    .extract()
    // .setPublicPath('public')
    .options({
        legacyNodePolyfills: true
    })

if (mix.inProduction()) {
    mix.version();
    // Set production environment variables
    process.env.MIX_APP_URL2 = 'https://myfamilyplatform.com/';
}

mix.webpackConfig({
    optimization: {
        splitChunks: {
            chunks: 'async',
            minSize: 20000,
            minChunks: 1,
            maxAsyncRequests: 30,
            maxInitialRequests: 30,
            enforceSizeThreshold: 50000,
            cacheGroups: {
                defaultVendors: {
                    test: /[\\/]node_modules[\\/]/,
                    priority: -10
                },
                default: {
                    minChunks: 2,
                    priority: -20,
                    reuseExistingChunk: true
                }
            }
        }
    }
});

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