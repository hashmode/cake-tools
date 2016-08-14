<?php 
use Cake\Core\Configure;
use Cake\Event\EventManager;
use CakeTools\Event\AfterSaveListener;
use CakeTools\Event\InitializeEventListener;

/**
 * set listeners
 */
$pluginsListeners = Configure::read('PluginsListeners');
if (empty($pluginsListeners)) {
    $pluginsListeners = [];
}

$pluginsListeners[] = new InitializeEventListener();
$pluginsListeners[] = new AfterSaveListener();
Configure::write('PluginsListeners', $pluginsListeners);

/**
 * attach to global event manager
 */
EventManager::instance()->on(new AfterSaveListener());
EventManager::instance()->on(new InitializeEventListener());
