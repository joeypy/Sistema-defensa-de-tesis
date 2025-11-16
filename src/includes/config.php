<?php
// Configuración de rutas base
if (!defined('BASE_URL')) {
    // En Docker, el DocumentRoot es /var/www/html
    // Las rutas absolutas empiezan desde la raíz del servidor
    // Por lo tanto, BASE_URL siempre será vacío (rutas desde /)
    define('BASE_URL', '');
    define('ASSETS_URL', '/src/assets');
    define('PAGES_URL', '/src/pages');
}

