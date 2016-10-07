<?php
namespace CakeTools\Controller;

use App\Controller\AppController as BaseController;
use Cake\Event\Event;

/**
 * Plugin's Controller
 *
 * @property      \CakeTools\Controller\Component\SecurityToolsComponent $SecurityTools
 * @property      \CakeTools\Controller\Component\AssistantComponent $Assistant
 */
class AppController extends BaseController
{

    public function initialize()
    {
        parent::initialize();
        
        $this->loadComponent('CakeTools.SecurityTools');
        $this->loadComponent('CakeTools.Assistant');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
    }

}
