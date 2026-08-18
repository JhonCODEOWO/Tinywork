import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        
    ],
    publicDir: false,
    build: {
        rolldownOptions: {
            input: {
                main: './src/main.js',
            },
            output: {
                entryFileNames: (chunkInfo) => {
                    const noHashFiles = ['main'];

                    if(noHashFiles.includes(chunkInfo.name)) {
                        return '[name].js';
                    }

                    return '[name]-[hash].js'
                },
                chunkFileNames: "[name]-[hash].js",
                assetFileNames: "[name].[ext]",
            }
        },
        outDir: 'public/build/',
        minify: true,
    }
})