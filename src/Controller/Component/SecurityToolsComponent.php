<?php
namespace CakeTools\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Exception\Exception;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Utility\Security;

/**
 * SecurityTools Component
 *
 * @property \Cake\Controller\Component\CookieComponent $Cookie
 */
class SecurityToolsComponent extends Component
{
    public $components = [
        'Cookie'
    ];

    /**
     *
     * @property \CakeTools\Controller\AppController
     */
    public $controller = null;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->controller = $this->_registry->getController();
    }

    /** 
     * unlockedActions method
     * 
     * @param array $actions
     * @return void
     */
    public function unlockedActions($actions = [])
	{
	    if (is_string($actions)) {
	        $actions = [
	            $actions
	        ];
	    }
	    
	    if (in_array($this->request->params['action'], $actions)) {
	        $this->controller->eventManager()->off($this->controller->Csrf);
            $this->controller->Security->config('unlockedActions', $actions);
	    }
	}

    /**
     * checkPassword method
     * checks of the given plain text password belongs to the current user
     *
     * @param string $password
     *            - plaintext password
     * @param string $hash
     *            - password should be provided if user is not logged in yet,
     * @return bool
     */
    public function checkPassword($password = '', $hash = null)
    {
        $userId = $this->controller->Auth->user('id');
        
        if (! $hash && !$userId) {
            throw new Exception('Either user should be logged in or user data should be provided');
        }
        
        if (! $hash) {
            $usersTable = TableRegistry::get('Users');
            $hash = $usersTable->get($userId, [
                'fields' => 'password'
            ]);
            
            if (empty($hash)) {
                return false;
            }
        }
        
        $key = Configure::read('CakeTools.security.password_key');
        
        if (!$key) {
            throw new Exception('password_key is not set');
        }
        
        $hash = Security::decrypt(base64_decode($hash), $key, '');
        $password = Security::hash($password, 'sha256');
        
        return password_verify($password, $hash);
    }

    /**
     * getPasswordData method
     * returns the password's bcyrpt hash - encrypted by AES256 and base64 encoded
     *
     * initial hash, just to remove bcrypt's length restriction (no salt needed at this stage !!)
     * 
     * @link http://security.stackexchange.com/a/6627/38200
     *      
     * @param string $password
     *            - plain text password to hash
     * @return string
     */
    public function getPasswordHash($password = '')
    {
        if (empty($password)) {
            throw new Exception('Password is required');
        }
        
        $key = Configure::read('CakeTools.security.password_key');
        
        if (!$key) {
            throw new Exception('password_key is not set');
        }

        $password = Security::hash($password, 'sha256');
        $cost = Configure::read('CakeTools.security.bcrypt_cost');
        
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
		$hash = base64_encode(Security::encrypt($hash, $key, ''));

		return $hash;
	}

    /**
     * getRandomString method
     * get random string using /dev/urandom
     *
     * @link http://security.stackexchange.com/a/3939/38200
     *      
     * @param number $length            
     * @param string|boolean $hash
     *            - if false base64 encoded string will be returned,
     *            if true - sha256,
     *            if string ('md5', 'sha1', 'sha256', 'sha384', 'sha512') hash function will be applied
     * @throws Exception - if urandom can not be used or invalid hashing algorithm is provided
     * @return string
     *
     */
    public function getRandomString($length = null, $hash = false)
    {
        if (! $length) {
            $length = 50;
        }

		$fp = @fopen('/dev/urandom','rb');
		if ($fp === false) {
			throw new Exception('Can not use urandom');
		}

		$pr_bits = @fread($fp, $length);
		@fclose($fp);
		
		if (!$pr_bits) {
			throw new Exception('Unable to read from urandom');
		}
		
		if ($hash) {
			if (is_bool($hash)) {
				return Security::hash($pr_bits, 'sha256', true);
			} elseif (in_array($hash, ['md5', 'sha1', 'sha256', 'sha384', 'sha512'])) {
				return Security::hash($pr_bits, $hash, true);
			} else {
				throw new Exception('Invalid hashing algorithm '.$hash);
			}
		}

		return substr(base64_encode($pr_bits), 0, $length);
	}
	
	
	/**
	 * getUniqueToken method
	 * 
	 * @param string $long
	 * @return string
	 */
	public function getUniqueToken($long = true)
	{
	    return $this->getRandomString(300, $long ? 'sha512' : 'sha256');
	}
	
	
	/**
	 * checkUserPermission method - checks if the record belongs to the user for the given model
	 *
	 * @param int|array $id - id or array as list of ids
	 * @param string $alias
	 * @param array $fields
	 * @return bool|array
	 */
	public function checkUserPermission($id = null, $alias = null, $fields = [])
	{
	    if (!$id || !$alias || !is_numeric_list($id)) {
	        return false;
	    }

	    $userId = $this->controller->Auth->user('id');
	    $model = TableRegistry::get($alias);
	    if (!$userId || !$model->hasField('user_id')) {
	        return false;
	    }
	
	    $options = [
	        'conditions' => [
	            'id' => $id,
	            'user_id' => $userId
	        ]
	    ];
	
	    if (is_array($id)) {
	        return $model->find('count', $options) == count($id) ? true : false;
	    }
	
	    $options['fields'] = ['id'];
	    if (!empty($fields)) {
	        $options['fields'] = array_merge(['id'], $fields);
	    }
	
	    return $model->find('all', $options)->first();
	}
	
	
	
}
