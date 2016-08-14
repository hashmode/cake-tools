<?php
use Cake\Routing\Router;
use Cake\Core\Configure;

$path = Configure::read('CakeTools.config.router_path');

Router::plugin('CakeTools', [
    'path' => '/' . $path
], function ($routes) {
    $routes->fallbacks('DashedRoute');
});
