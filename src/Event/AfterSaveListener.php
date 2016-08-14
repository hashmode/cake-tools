<?php
namespace CakeTools\Event;

use Cake\Event\EventListenerInterface;
use Cake\Event\Event;
use Cake\ORM\Entity;

/**
 * AfterSave Listener
 */
class AfterSaveListener implements EventListenerInterface
{

    public function implementedEvents()
    {
        return [
            'Model.afterSave' => 'afterSave'
        ];
    }

    public function afterSave(Event $event, Entity $entity, \ArrayObject $options)
    {
        if ($entity->isNew()) {
            $event->subject()->insertedIds($entity->id);
        }
        
        if (! empty($entity->id)) {
            $event->subject()->savedIds($entity->id);
        }

        return true;
    }
}
