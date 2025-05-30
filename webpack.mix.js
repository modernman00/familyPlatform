const path = require('path');
const mix = require('laravel-mix');
const CopyWebpackPlugin = require('copy-webpack-plugin'); // Copies files (like images) to your public folder.
const ImageminPlugin = require('imagemin-webpack-plugin').default; // Optimizes images.

mix
  .setPublicPath('public') // Everything goes into the /public folder
  //   .setResourceRoot('./') // This tells where to load resources from (like images in CSS)
  .sass('resources/asset/scss/main.scss', 'css') // Turn SCSS into public/css/main.css
  .js('resources/asset/js/index.js', 'js') // Turn index.js into public/js/index.js
  .extract(); // Pull out vendor code (like from node_modules) into a separate file for caching

if (mix.inProduction()) {
  mix.version();
}
if (!mix.inProduction()) {
  mix.sourceMaps();
  //This helps debugging with correct source lines in the browser.
}
/**Shared or vendor libraries are split into separate files

Your shared-lib package is its own chunk for reuse and caching 
# Ensure shared-lib is its own chunk even if small or also in node_modules*/
mix.webpackConfig({
  optimization: {
    splitChunks: {
      chunks: 'all', // Split all types of files
      minSize: 20000,
      minChunks: 1,
      maxAsyncRequests: 30,
      maxInitialRequests: 30,
      //   enforceSizeThreshold: 50000,
      cacheGroups: {
        sharedLib: {
          test: /[\\/]node_modules[\\/]@modernman00[\\/]shared-js-lib[\\/]/,
          name: 'shared-lib',
          priority: 20,
          enforce: true,
        },
        default: {
          minChunks: 2,
          priority: -20,
          reuseExistingChunk: true,
        },
      },
    },
  },
  //This makes your imports cleaner and shorter in your JS files.
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/asset/js'),
      '@scss': path.resolve(__dirname, 'resources/asset/scss'),
      '@css': path.resolve(__dirname, 'resources/asset/css'),
      '@img': path.resolve(__dirname, 'resources/asset/img'),
      '@components': path.resolve(__dirname, 'resources/asset/js/components'),
      '@shared': path.resolve(
        __dirname,
        'node_modules/@modernman00/shared-js-lib',
      ),
    },
  },
  output: {
    chunkFilename: 'js/chunks/[name].js',
    filename: '[name].js',
    publicPath: '/public/', // Explicitly include 'public' in the URL
  },
  plugins: [
    new CopyWebpackPlugin({
      patterns: [
        {
          from: 'resources/asset/images', // Source directory
          to: 'img/webImg', // Output directory (relative to public/)
          globOptions: {
            ignore: ['**/.DS_Store'], // Ignore macOS metadata files
          },
        },
      ],
    }),
    new ImageminPlugin({
      test: /\.(jpe?g|png|gif|svg)$/i,
      pngquant: {
        quality: '70-90', // Adjust PNG quality (lower = smaller files)
      },
      jpegtran: {
        progressive: true, // Progressive JPEGs (better compression)
      },
      gifsicle: {
        optimizationLevel: 3, // GIF optimization (1-3)
      },
      svgo: {
        // SVG optimization
        plugins: [
          { removeViewBox: false }, // Keep viewBox for responsive SVGs
          { removeDimensions: true }, // Remove width/height if viewBox exists
        ],
      },
    }),
  ],
});

mix.babelConfig({
  plugins: ['@babel/plugin-syntax-dynamic-import'], // Enables `import()` syntax for code-splitting
});
