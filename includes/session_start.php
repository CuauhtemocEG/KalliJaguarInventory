<?php
    session_name("INV");
    
    // Configurar parámetros de cookie antes de iniciar la sesión
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '.kallijaguar-inventory.com',
        'secure' => false,  // Cambiar a true si usas HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();