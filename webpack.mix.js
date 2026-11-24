const fs = require('fs');
const path = require('path');
const mix = require('laravel-mix');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;

mix
  .setPublicPath('public')
  .js('resources/asset/js/index.js', 'js')
  .js('resources/asset/js/apps/studio/index.jsx', 'js/studio-app.js')
  .js('resources/asset/js/apps/organogram/index.jsx', 'js/organogram-app.js')
  .js('resources/asset/js/apps/feed/index.jsx', 'js/feed-app.js')
  .js('resources/asset/js/apps/profile/index.jsx', 'js/profile-app.js')
  .js('resources/asset/js/apps/arcade/index.jsx', 'js/arcade-app.js')
  .js('resources/asset/js/components/livingPhotoAvatar.js', 'js/atg-living-photo-avatar.js');

if (fs.existsSync(path.resolve(__dirname, 'resources/asset/js/apps/account-settings/index.jsx'))) {
  mix.js('resources/asset/js/apps/account-settings/index.jsx', 'js/account-settings-app.js');
}

mix
  .sass('resources/asset/scss/main.scss', 'css')
  .options({
    legacyNodePolyfills: true,
    processCssUrls: false,
    progress: false
  })
  .extract((module) => {
    const name = module.nameForCondition && module.nameForCondition();
    if (!name || !/[\\/]node_modules[\\/]/.test(name)) return false;
    if (/[\\/]node_modules[\\/](three|hls\.js|gsap|emojibase-data)[\\/]/.test(name)) {
      return false;
    }
    return true;
  });

const devtool = mix.inProduction()
  ? false
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
    })
  );
  return {
    plugins,
    // The ?v=<hash> on lazy chunks stops the service worker's stale-while-revalidate cache
    // (and browser caches) from serving an old profilePage.js after a deploy.
    output: { publicPath: "/public/", chunkFilename: "js/[name].js?v=[contenthash:8]" },
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
          default: {
            minChunks: 2,
            priority: -20,
            reuseExistingChunk: true,
          },
        },
      },
    },
    module: {
      parser: {
        javascript: {
          exportsPresence: "warn",
        },
      },
    },
    resolve: {
      symlinks: false,
      extensions: ['.js', '.jsx', '.json'],
      modules: [
        path.resolve(__dirname, "node_modules"),
        "node_modules"
      ],
      alias: {
        "react": "preact/compat",
        "react-dom": "preact/compat",
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
        "fs": false,
        "buffer": false,
        "process": false
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
    }],
    ['@babel/preset-react', {
      runtime: 'automatic',
      importSource: 'preact'
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
  
  // Automate Service Worker versioning per deployment
  mix.then(() => {
    const fs = require('fs');
    const swPath = path.join(__dirname, 'service-worker.js');
    if (fs.existsSync(swPath)) {
      let swContent = fs.readFileSync(swPath, 'utf8');
      const newVersion = Date.now().toString();
      swContent = swContent.replace(/const Version = ".*";/, `const Version = "${newVersion}";`);
      fs.writeFileSync(swPath, swContent);
      console.log(`\n[Service Worker] Bumped version to ${newVersion}`);
    }
  });
}

mix.override((config) => {
  config.plugins = config.plugins.filter(plugin => 
    plugin.constructor.name !== 'ProgressPlugin' && 
    plugin.constructor.name !== 'WebpackBarPlugin'
  );
  // Only extract synchronously-imported node_modules into the global vendor.js chunk.
  // Dynamically imported heavy engines (three, hls.js, emojibase-data) stay in on-demand async chunks.
  const cacheGroups = config.optimization?.splitChunks?.cacheGroups;
  if (cacheGroups) {
    Object.keys(cacheGroups).forEach((key) => {
      if (key.startsWith('vendor') || key === 'defaultVendors') {
        cacheGroups[key].chunks = 'initial';
      }
    });
  }
});