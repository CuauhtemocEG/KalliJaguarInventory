<?php
    session_name("INV");
    
    // Determinar si estamos en HTTPS
    $isHTTPS = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    session_set_cookie_params([
        'lifetime' => 0,                    
        'path' => '/',                      
        'domain' => '',                     
        'secure' => $isHTTPS,              // Solo seguro si estamos en HTTPS
        'httponly' => true,                 
        'samesite' => 'Lax'              
    ]);
    
    session_start();