const path = require('path');
const mix = require('laravel-mix');
const CopyWebpackPlugin = require('copy-webpack-plugin'); // Copies files (like images) to your public folder.
const ImageminPlugin = require('imagemin-webpack-plugin').default; // Optimizes images.

mix
  .setPublicPath('public') // Everything goes into the /public folder
  //   .setResourceRoot('./') // This tells where to load resources from (like images in CSS)
  .sass('resources/asset/scss/main.scss', 'css') // Turn SCSS into public/css/main.css
  .js('resources/asset/js/index.js', 'js') // Turn index.js into public/js/index.js
  .options({ legacyNodePolyfills: true })
  .extract(); // Pull out vendor code (like from node_modules) into a separate file for caching


// Custom devtool based on environment
const devtool = mix.inProduction() 
  ? 'source-map'         // Production (full source maps)
  : 'cheap-module-source-map'; // Development (CSP-friendly)

mix
  .setPublicPath("public")
  .webpackConfig({
    output: { publicPath: "/public/" },
    devtool, // <-- Add this line to configure sourcemap type
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
    //This makes your imports cleaner and shorter in your JS files.
    resolve: {
      alias: {
        "@": path.resolve(__dirname, "resources/asset/js"),
        "@scss": path.resolve(__dirname, "resources/asset/scss"),
        "@css": path.resolve(__dirname, "resources/asset/css"),
        "@img": path.resolve(__dirname, "resources/asset/img"),
        "@components": path.resolve(__dirname, "resources/asset/js/components"),
        "@shared": path.resolve(
          __dirname,
          "node_modules/@modernman00/shared-js-lib"
        ),
      },
    },
    plugins: [
      new CopyWebpackPlugin({
        patterns: [
          {
            from: "resources/asset/images", // Source directory
            to: "img/webImg", // Output directory (relative to public/)
            globOptions: {
              ignore: ["**/.DS_Store"], // Ignore macOS metadata files
            },
          },
        ],
      }),
      new ImageminPlugin({
        test: /\.(jpe?g|png|gif|svg)$/i,
        pngquant: {
          quality: "70-90", // Adjust PNG quality (lower = smaller files)
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
    ]
  })

// Enable versioning in production
if (mix.inProduction()) {
  mix.version();
}

if (process.env.MIX_SOURCEMAPS !== 'false') {
  mix.webpackConfig({ devtool });
}

mix.babelConfig({
  plugins: ["@babel/plugin-syntax-dynamic-import", "@babel/plugin-proposal-optional-chaining"],
});