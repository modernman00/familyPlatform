import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import preact from '@preact/preset-vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

function copyDirSync(src, dest) {
    if (!fs.existsSync(src)) return;
    fs.mkdirSync(dest, { recursive: true });
    for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
        if (entry.name === '.DS_Store') continue;
        const srcPath = path.join(src, entry.name);
        const destPath = path.join(dest, entry.name);
        if (entry.isDirectory()) {
            copyDirSync(srcPath, destPath);
        } else {
            fs.copyFileSync(srcPath, destPath);
        }
    }
}

function md5File(filePath) {
    const buf = fs.readFileSync(filePath);
    return crypto.createHash('md5').update(buf).digest('hex');
}

function familyPlatformBuildPlugin(isProd) {
    return {
        name: 'family-platform-build-plugin',
        closeBundle() {
            const publicDir = path.resolve(__dirname, 'public');
            const imagesSrc = path.resolve(__dirname, 'resources/asset/images');
            const imagesDest = path.join(publicDir, 'img/webImg');
            copyDirSync(imagesSrc, imagesDest);

            // Preserve manifest.js / vendor.js compatibility shims for legacy Blade layouts
            const jsDir = path.join(publicDir, 'js');
            fs.mkdirSync(jsDir, { recursive: true });
            const shimContent = '/* Vite ES module build compatibility shim */\n';
            fs.writeFileSync(path.join(jsDir, 'manifest.js'), shimContent);
            fs.writeFileSync(path.join(jsDir, 'vendor.js'), shimContent);

            // Emit mix-manifest.json compatible with Gate 4 production artefact checks
            const manifestEntries = [
                '/js/index.js',
                '/js/studio-app.js',
                '/js/organogram-app.js',
                '/js/feed-app.js',
                '/js/profile-app.js',
                '/js/arcade-app.js',
                '/js/atg-living-photo-avatar.js',
                '/js/account-settings-app.js',
                '/js/manifest.js',
                '/js/vendor.js',
                '/css/main.css',
            ];
            const mixManifest = {};
            for (const rel of manifestEntries) {
                const abs = path.join(publicDir, rel.replace(/^\//, ''));
                if (fs.existsSync(abs)) {
                    const hash = md5File(abs);
                    mixManifest[rel] = isProd ? `${rel}?id=${hash}` : rel;
                }
            }
            fs.writeFileSync(
                path.join(publicDir, 'mix-manifest.json'),
                JSON.stringify(mixManifest, null, 4)
            );

            // Sync public/sw.js SW_VERSION from mix-manifest.json hash on production builds
            if (isProd) {
                const swPath = path.resolve(publicDir, 'sw.js');
                if (fs.existsSync(swPath)) {
                    const manifestHash = crypto
                        .createHash('md5')
                        .update(JSON.stringify(mixManifest))
                        .digest('hex')
                        .slice(0, 8);
                    const targetVersion = `build-${manifestHash}`;
                    const swContent = fs.readFileSync(swPath, 'utf8');
                    const updatedContent = swContent.replace(
                        /^const SW_VERSION = '[^']*';/m,
                        `const SW_VERSION = '${targetVersion}';`
                    );
                    if (updatedContent !== swContent) {
                        fs.writeFileSync(swPath, updatedContent);
                        console.log(`\n[Service Worker] Synced public/sw.js SW_VERSION to ${targetVersion}`);
                    }
                }
            }
        },
    };
}

export default defineConfig(({ mode }) => {
    const isProd = mode === 'production';

    const input = {
        index: path.resolve(__dirname, 'resources/asset/js/index.js'),
        'studio-app': path.resolve(__dirname, 'resources/asset/js/apps/studio/index.jsx'),
        'organogram-app': path.resolve(__dirname, 'resources/asset/js/apps/organogram/index.jsx'),
        'feed-app': path.resolve(__dirname, 'resources/asset/js/apps/feed/index.jsx'),
        'profile-app': path.resolve(__dirname, 'resources/asset/js/apps/profile/index.jsx'),
        'arcade-app': path.resolve(__dirname, 'resources/asset/js/apps/arcade/index.jsx'),
        'atg-living-photo-avatar': path.resolve(__dirname, 'resources/asset/js/components/livingPhotoAvatar.js'),
    };

    const accountSettingsEntry = path.resolve(__dirname, 'resources/asset/js/apps/account-settings/index.jsx');
    if (fs.existsSync(accountSettingsEntry)) {
        input['account-settings-app'] = accountSettingsEntry;
    }

    return {
        base: '/public/',
        publicDir: false,
        plugins: [
            preact({
                reactAliasesEnabled: true,
            }),
            familyPlatformBuildPlugin(isProd),
        ],
        resolve: {
            extensions: ['.js', '.jsx', '.json'],
            alias: {
                react: 'preact/compat',
                'react-dom': 'preact/compat',
                '@': path.resolve(__dirname, 'resources/asset/js'),
                '@scss': path.resolve(__dirname, 'resources/asset/scss'),
                '@css': path.resolve(__dirname, 'resources/asset/css'),
                '@img': path.resolve(__dirname, 'resources/images/img'),
                '@imgProfile': path.resolve(__dirname, 'resources/images/profile'),
                '@components': path.resolve(__dirname, 'resources/asset/js/components'),
                '@shared': path.resolve(__dirname, 'node_modules/@modernman00/shared-js-lib'),
            },
        },
        css: {
            preprocessorOptions: {
                scss: {
                    quietDeps: true,
                    silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'color-functions', 'slash-div'],
                },
            },
        },
        build: {
            outDir: 'public',
            emptyOutDir: false,
            sourcemap: !isProd,
            minify: isProd,
            cssCodeSplit: false,
            rollupOptions: {
                input,
                output: {
                    format: 'es',
                    entryFileNames: 'js/[name].js',
                    chunkFileNames: 'js/[name]-[hash].js',
                    assetFileNames: (assetInfo) => {
                        const name = assetInfo.name || '';
                        if (name.endsWith('.css')) {
                            return 'css/main.css';
                        }
                        return 'assets/[name]-[hash][extname]';
                    },
                },
            },
        },
    };
});
