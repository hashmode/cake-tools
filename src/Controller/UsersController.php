<?php
namespace CakeTools\Controller;

use CakeTools\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;

/**
 * Users Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @property \CakeTools\Controller\Component\AuthToolsComponent $AuthTools
 */
class UsersController extends AppController
{
    /**
     * {@inheritDoc}
     * @see \CakeTools\Controller\AppController::initialize()
     */
    public function initialize()
    {
        parent::initialize();
        
        $this->loadComponent('CakephpCaptcha.Captcha');
        $this->loadComponent('CakeTools.AuthTools');
    }

    /**
     * {@inheritDoc}
     * @see \CakeTools\Controller\AppController::beforeFilter()
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        
        $this->Auth->allow([
            'signin',
            'captchaImage',
            'logout'
        ]);
    }

    /**
     * signin method
     * 
     * @return \Cake\Network\Response|NULL
     */
    public function signin()
    {
        $validCaptcha = $this->AuthTools->checkCaptcha();
        if ($this->request->is('post')) {
            $validCaptcha = $this->AuthTools->checkCaptcha($this->request->data);
            $userData = $this->AuthTools->getUserData($this->request->data);
            
            $errorMessage = __('Invalid Email and/or Password');
            if (empty($userData) || ! $validCaptcha) {
                if ($validCaptcha) {
                    $errorMessage = __('Invalid credentials');
                } else {
                    $errorMessage = __('Invalid credentials and/or captcha');
                }
            } else {
                if ($this->AuthTools->login($this->request->data)) {
                    $this->Flash->success(__('Successful Login'));
                    return $this->redirect($this->Auth->redirectUrl());
                }
            }
            
            $this->Flash->error($errorMessage);
        }
    }
    

    /**
     * captchaImage method
     * 
     */
    public function captchaImage()
    {
        $this->autoRender = false;
        echo $this->AuthTools->captchaImage();
        die();
    }


    /**
     * logout method
     * 
     */
    public function logout()
    {
        $this->redirect($this->Auth->logout());
    }
    
}

