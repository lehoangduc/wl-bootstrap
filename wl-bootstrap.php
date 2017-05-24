<?php
/**
 * @package WL_Bootstrap
 * @version 1.0
 */
/*
Plugin Name: WL_Bootstrap
Description: This plugin allows you to use any functions, methods, libraries of Laravel in WordPress project
Author: Duc Le
Version: 1.0
Author URI: https://engineering.golr.xyz
*/

function wl_bootstrap() {
    if (!defined('LARAVEL_PATH')) {
        throw new Exception('LARAVEL_PATH is not configured.');
    }

    require LARAVEL_PATH . '/bootstrap/autoload.php';

    $app = require_once LARAVEL_PATH . '/bootstrap/app.php';

    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = \Illuminate\Http\Request::capture();

    $app->instance('request', $request);
    $kernel->bootstrap();

    $response = (new \Illuminate\Routing\Pipeline($app))
        ->send($request)
        ->through([
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class
        ])
        ->then(function () {
            return response('', 200, []);
        });

    // Set cookie from response headers
    foreach ($response->headers->getCookies() as $cookie) {
        if ($cookie->isRaw()) {
            setrawcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        } else {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }
    }
}

add_action('wp', 'wl_bootstrap');
