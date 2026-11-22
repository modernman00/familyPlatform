import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vitest/config';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    oxc: {
        jsx: {
            runtime: 'automatic',
            importSource: 'preact',
        },
    },
    resolve: {
        alias: {
            react: 'preact/compat',
            'react-dom': 'preact/compat',
            '@': path.resolve(__dirname, 'resources/asset/js'),
            '@shared': path.resolve(__dirname, 'node_modules/@modernman00/shared-js-lib'),
        },
    },
    test: {
        environment: 'jsdom',
        include: ['resources/asset/js/apps/**/*.test.{js,jsx}'],
        globals: true,
        fileParallelism: false,
        isolate: false,
    },
});
