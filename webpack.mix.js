const path = require('path');
const mix = require('laravel-mix');

mix.sass('resources/asset/scss/main.scss', 'public/style.css')
    .js('resources/asset/js/index.js', 'public/index.js')
    .extract()
    .setPublicPath('public')
    .options({
        legacyNodePolyfills: true
    })

if (mix.inProduction()) {
    mix.version();
    // Set production environment variables
    process.env.MIX_APP_URL = 'https://myfamilyplatform.com/';
}

mix.webpackConfig({
    resolve: {
           alias: {
               '@shared': path.resolve(__dirname, 'node_modules/@modernman00/shared-js-lib')
           }
       },
    optimization: {
        splitChunks: {
            chunks: 'all',
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
                sharedLib: {
                       test: /[\\/]node_modules[\\/]@modernman00[\\/]shared-js-lib[\\/]/,
                       name: 'shared-lib',
                       priority: 20,
                       enforce: true
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