import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // Adicione o novo arquivo CSS aqui
            input: [
                'resources/css/app.css', // Seu CSS principal do sistema interno
                'resources/css/jm-celulares-site.css', // CSS para a página pública
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});