<?php
namespace CakeTools\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * Assistant Component
 * 
 * @property    \CakephpCaptcha\Controller\Component\CaptchaComponent $Captcha
 * @property    \CakeTools\Controller\Component\SecurityToolsComponent $SecurityTools
 */
class AuthToolsComponent extends Component
{

    public $components = [
        'SecurityTools',
        'Captcha'
    ];
    
    /**
     * @var boolean
     */
    protected $showCaptcha = false;
    
    /**
     * @var boolean
     */
    protected $validCaptcha = true;
    
    /**
     * @var array
     */
    protected $authFields = [];
    
    /**
     * @var mixed
     */
    protected $loginSettings = [];

    /**
     * @var mixed
     */
    protected $userData = null;
    
    /**
     *
     * @var \CakeTools\Controller\AppController
     */
    protected $controller = null;
    
    public function initialize(array $config)
    {
        parent::initialize($config);
        
        $this->controller = $this->_registry->getController();
    }
    
    
    /**
     * startup method
     * 
     * @param Event $event
     */
    public function startup(Event $event)
    {
        $this->authFields = $this->controller->Auth->config('authenticate.Form.fields');
        if (empty($this->authFields)) {
            $this->authFields = [
                'username' => 'email',
                'password' => 'password'
            ];
        }

        $this->loginSettings = Configure::read('CakeTools.security.login');
    }
    
    
    /**
     * beforeRender method
     * 
     * @param Event $event
     */
    public function beforeRender(Event $event)
    {
        $this->controller->set([
            'authFields' => $this->authFields,
            'showCaptcha' => $this->showCaptcha
        ]);
    }
    
    
    /**
     * getFields method
     *
     * @return mixed|\Cake\Controller\Component\AuthComponent|NULL|unknown
     */
    public function getFields()
    {
        return $this->authFields;
    }
    
    
    /**
     * checkCaptcha method
     * 
     * @param array $rd
     * @return boolean
     */
    public function checkCaptcha(array $rd = [])
    {
        /** 
         * if captcha is disabled - all good
         */
        if ($this->loginSettings['field'] === false) {
            return true;
        }

        /**
         * for post request 
         */
        if ($this->controller->request->is('post') && !empty($rd)) {
            $userData = $this->getUserData($rd);
            
            if (isset($rd['captcha'])) {
                // if exists - show again (if successfull login - will be redirected)
                $this->showCaptcha = true;
            
                if ($this->Captcha->check($rd['captcha'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $count = $this->loginSettings['limit'];
                if (isset($userData->{$this->loginSettings['field']}) && $userData->{$this->loginSettings['field']} >= $count) {
                    $this->showCaptcha = true;
                    return false;
                }
            }
        }
        
        /** 
         * for get request
         */
        if ($this->loginSettings['field'] === true) {
            // always show captcha
            $this->showCaptcha = true;
        }

        return true;
    }
    

    /**
     * getUserData method
     *
     * @param array $rd            
     */
    public function getUserData(array $rd = [])
    {
        if (! empty($this->userData)) {
            return $this->userData;
        }
        
        $username = $rd[$this->authFields['username']];
        $password = $rd[$this->authFields['password']];
        
        $query = $this->controller->Users->find()
            ->where([
            'LOWER(Users.' . $this->authFields['username'] . ')' => strtolower($username)
        ])
            ->select([
            'id',
            $this->authFields['username'],
            $this->authFields['password']
        ]);
        
        if (!empty($this->loginSettings['conditions'])) {
            $query->where($this->loginSettings['conditions']);
        }
            
        if ($this->controller->Users->hasField('group_id')) {
            $query->select([
                'group_id'
            ]);
        }
        
        if (is_string($this->loginSettings['field'])) {
            $query->select([
                $this->loginSettings['field']
            ]);
        }
        
        $this->userData = $query->first();
        return $this->userData;
    }
    
    
    /**
     * login method
     * 
     * @param array $rd
     * @return boolean
     */
    public function login(array $rd = [])
    {
        $userData = $this->getUserData($rd);
        $password = $rd[$this->authFields['password']];

        // data to use to login - will be saved in session
        $loginUserData = [
            'id' => $userData->id,
            $this->authFields['username'] => $userData->{$this->authFields['username']}
        ];
        
        if (isset($userData->group_id)) {
            $loginUserData['group_id'] = $userData->group_id;
        }
        
        if ($this->SecurityTools->checkPassword($password, $userData->{$this->authFields['password']})) {
            // reset failed login count
            if (is_string($this->loginSettings['field'])) {
                $data = [
                    'id' => $userData->id,
                    $this->loginSettings['field'] => 0
                ];
                
                $this->controller->Users->saveOne($data, [
                    'check' => false
                ]);
            }

            $this->controller->Auth->setUser($loginUserData);

            return true;
        } else {
            if (is_string($this->loginSettings['field'])) {
                $data = [
                    'id' => $userData->id,
                    $this->loginSettings['field'] => $userData->{$this->loginSettings['field']} + 1
                ];

                $this->controller->Users->saveOne($data, ['check' => false]);

                // if this is the n-th wrong attempt - show captcha
                if ($userData->{$this->loginSettings['field']} >= $this->loginSettings['limit']) {
                    $this->showCaptcha = true;
                }
            }
        }
    }

    /**
     * captchaImage method
     * 
     * @return string
     */
    public function captchaImage()
    {
        return $this->Captcha->image($this->loginSettings['count']);
    }
    
}