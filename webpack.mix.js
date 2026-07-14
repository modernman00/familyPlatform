const path = require('path');
const mix = require('laravel-mix');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;

mix
  .setPublicPath('public')
  .js('resources/asset/js/index.js', 'js')
  .sass('resources/asset/scss/main.scss', 'css')
  .options({
    legacyNodePolyfills: true,
    processCssUrls: false,
    progress: false
  })
  .extract();

const devtool = mix.inProduction()
  ? 'source-map'
  : 'cheap-module-source-map';

mix.webpackConfig((webpack, config) => {
  const plugins = config.plugins;

  // Add all your other plugins
  plugins.push(
    new CopyWebpackPlugin({
      patterns: [
        {
          from: "resources/asset/images",
          to: "img/webImg",
          globOptions: {
            ignore: ["**/.DS_Store"],
          },
        },
      ],
    }),
    new ImageminPlugin({
      test: /\.(jpe?g|png|gif|svg)$/i,
      pngquant: {
        quality: "70-90",
      },
      jpegtran: {
        progressive: true,
      },
      gifsicle: {
        optimizationLevel: 3,
      },
      svgo: {
        plugins: [
          { removeViewBox: false },
          { removeDimensions: true },
        ],
      },
    })
  );
  return {
    plugins,
    output: { publicPath: "/public/" },
    devtool,
    optimization: {
      splitChunks: {
        chunks: "async",
        minSize: 20000,
        minChunks: 1,
        maxAsyncRequests: 30,
        maxInitialRequests: 30,
        enforceSizeThreshold: 50000,
        cacheGroups: {
          defaultVendors: {
            test: /[\\/]node_modules[\\/]/,
            priority: -10,
          },
          default: {
            minChunks: 2,
            priority: -20,
            reuseExistingChunk: true,
          },
        },
      },
    },
    resolve: {
      alias: {
        "@": path.resolve(__dirname, "resources/asset/js"),
        "@scss": path.resolve(__dirname, "resources/asset/scss"),
        "@css": path.resolve(__dirname, "resources/asset/css"),
        "@img": path.resolve(__dirname, "resources/images/img"),
        "@imgProfile": path.resolve(__dirname, "resources/images/profile"),
        "@components": path.resolve(__dirname, "resources/asset/js/components"),
        "@shared": path.resolve(__dirname, "node_modules/@modernman00/shared-js-lib"),
      },
      fallback: {
        "path": false,
        "fs": false
      }
    }
  };
});

mix.babelConfig({
  presets: [
    ['@babel/preset-env', {
      useBuiltIns: 'entry',
      corejs: 3,
      targets: {
        browsers: ['> 1%', 'last 2 versions', 'not dead', 'safari >= 9']
      }
    }]
  ],
  plugins: [
    "@babel/plugin-syntax-dynamic-import",
    "@babel/plugin-proposal-optional-chaining",
    "@babel/plugin-transform-runtime"
  ]
});

if (mix.inProduction()) {
  mix.version();
}

mix.override((config) => {
  config.plugins = config.plugins.filter(plugin => 
    plugin.constructor.name !== 'ProgressPlugin' && 
    plugin.constructor.name !== 'WebpackBarPlugin'
  );
});