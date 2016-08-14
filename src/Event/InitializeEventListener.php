<?php
namespace CakeTools\Event;

use Cake\Event\EventListenerInterface;
use Cake\Event\Event;

/**
 * InitializeEvent Listener
 */
class InitializeEventListener implements EventListenerInterface
{

    public function implementedEvents()
    {
        return [
            'Model.initialize' => 'initializeEvent'
        ];
    }

    public function initializeEvent(Event $event, $data = [], $options = [])
    {
        $event->subject()->addBehavior('CakeTools.Assistant');
    }
}
