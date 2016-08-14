<?php
namespace CakeTools\Model\Validation;

use Cake\Validation\Validator;
use Cake\Utility\Inflector;
use Cake\Core\Exception\Exception;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\ORM\Table;
use Cake\Validation\Validation;

/**
 * Global Validator
 *
 */
class GlobalValidator extends Validator
{
    /** 
     * @property Cake\Validation\Validator
     */
    protected $validator = null;
    
    /** 
     * @property Cake\ORM\Table
     */
    protected $_table = null;
    
    /**
     * __construct method
     * 
     * @param Validator $validator
     * @param Table $table
     * @throws Exception
     */
    public function __construct(Validator $validator = null, Table $table = null)
    {
        parent::__construct();
        
        if (empty($validator)) {
            throw new Exception('Validator is not provided');
        }
        
        if (empty($table)) {
            throw new Exception('Table is not provided');
        }

        $this->validator = $validator;
        $this->_table = $table;
    }

    
    
    /**
     *
     * @param array $fields
     * @return \CakeTools\Model\Validation\GlobalValidator
     */
    public function isRequired($fields = [])
    {
        foreach ($fields as $field => $humanField) {
            $this->validator->requirePresence($field, true, __('{0} is required', $humanField));
        }

        return $this;
    }
    
    
    /**
     * isNotEmpty method
     * adds notEmpty and notBlank validation rules to the given field
     *
     * @param string $field            
     * @return $this
     */
    public function isNotEmpty($field, $message = null, $humanField = null)
    {
        if (empty($message)) {
            $humanField = $humanField ? $humanField : Inflector::humanize($field);
            $message = __('{0} can not be empty', $humanField);
        }

        $this->validator->notEmpty($field, $message);
        $this->validator->add($field, [
            'notBlank' => [
                'rule' => 'notBlank',
                'message' => $message
		    ]
		]);
		
		return $this;
	}
    
    
    /** 
     * string method
     * adds a validation rule as notEmpty, notBlank and maxLength to the given field
     * 
     * @param string $field
     * @param number $maxLength
     * @param bool $allowEmpty
     * @return $this
     */
    public function string($field, $maxLength = null, $allowEmpty = false)
    {
        $humanField = Inflector::humanize($field);
        
        if (empty($maxLength)) {
            $maxLength = Configure::read('CakeTools.model.validation.length.varchar');
        }

        if ($allowEmpty) {
            $this->validator->allowEmpty($field);
        } else {
            $this->isNotEmpty($field);
        }

        $this->validator->add($field, [
            'maxLength' => [
                'rule' => [
                    'maxLength',
                    $maxLength
                ],
                'message' => __('{0} is too long. Max {1} characters are allowed', $humanField, $maxLength)
            ]
        ]);

        return $this;
    }
    
    
    /** 
     * text method
     * adds a validation rule as notEmpty, notBlank and maxLength to the given field
     * 
     * @param string $field
     * @param number $maxLength
     * @param bool $allowEmpty
     * @return $this
     */
    public function text($field, $maxLength = null, $allowEmpty = false)
    {
        if (empty($maxLength)) {
            $maxLength = Configure::read('CakeTools.model.validation.length.text');
        }
        
        $this->string($field, $maxLength, $allowEmpty);
        return $this;
    }
    
    
    /** 
     * isEmail method
     * adds a validation rule as notEmpty, notBlank, maxLength and email to the given field
     * 
     * @param string $field
     * @param number $maxLength
     * @param bool $allowEmpty
     * @return $this
     */
    public function isEmail($field = null, $maxLength = null, $allowEmpty = false)
    {
        if (empty($field)) {
            $field = 'email';
        }

        $this->validator->email($field, false, __('Invalid email'));

        if (empty($maxLength)) {
            $maxLength = Configure::read('CakeTools.model.validation.length.varchar');
        }
        
        $this->string($field, $maxLength, $allowEmpty);
        return $this;
    }
    
    
    /**
     * unique method
     * 
     * @param array|string $fields - if array - firt element should be the field to add rule
     * @param array $conditions
     * @param string $message
     * @return \CakeTools\Model\Validation\GlobalValidator
     */
    public function unique($fields, $conditions = [], $message = '')
    {
        if (is_string($fields)) {
            $fields = [
                $fields
            ];
        }

        $field = array_shift($fields);
        
        if (empty($message)) {
            $message = __('This {0} already exists', Inflector::humanize($field));
        }
        
        $this->validator->add($field, 'checkUnique', [
            'rule' => function ($value, $context) use ($field, $fields, $conditions) {
                $query = $this->_table->find()
                    ->where([
                    'LOWER(' . $field . ')' => mb_strtolower($value)
                ])
                    ->where($conditions);
                    
                // exclude this record
                if (!$context['newRecord'] && !empty($context['data']['id'])) {
                    $query->where(['id <>' => $context['data']['id']]);
                }
                
                if (! empty($fields)) {
                    $fieldsValues = array_intersect_key($context['data'], array_flip($fields));
                    
                    foreach ($fieldsValues as $f => $v) {
                        $query->where([
                            $f => $v
                        ]);
                    }
                }
                
                return $query->count() > 0 ? false : true;
            },
            'message' => $message
        ]);

        return $this;
    }


    /** 
     * foreignKey method
     * checks for the given foreign key field for notEmpty, notBlank and naturalNumber
     * 
     * @param string $field
     * @param bool $allowEmpty
     * @return $this
     */
    public function foreignKey($field, $allowEmpty = false, $message = null)
    {
        $humanField = Inflector::humanize(str_ireplace('_id', '', $field));

        if (empty($message)) {
            $message = __('Invalid {0}', $humanField);
        }

        if ($allowEmpty) {
            $this->validator->allowEmpty($field);
        } else {
            $this->isNotEmpty($field, null, $humanField);
        }
        
        $this->validator->add($field, [
        	'naturalNumber' => [
        		'rule' => 'naturalNumber',
        		'message' => $message
        	]
        ]);
        
        return $this;
    }
    

    /** 
     * isNumeric method
     * checks for the given foreign key field for notEmpty, notBlank and numeric/natural number
     * 
     * @param string $field
     * @param bool $naturalNumber
     * @param bool $allowEmpty
     * @return $this
     */
    public function isNumeric($field, $naturalNumber = false, $allowEmpty = false)
    {
        $humanField = Inflector::humanize($field);

        if ($allowEmpty) {
            $this->validator->allowEmpty($field);
        } else {
            $this->isNotEmpty($field);
        }

        if ($naturalNumber) {
            $this->validator->naturalNumber($field, __('{0} should be natural number', $humanField));
        } else {
            $this->validator->numeric($field, __('{0} should be numeric', $humanField));
        }

        return $this;
    }
    
    
    /**
     * isRange method
     * 
     * @param string $field
     * @param array $range
     * @param bool $allowEmpty
     * @param string $message
     * @return \CakeTools\Model\Validation\GlobalValidator
     */
    public function isRange($field, array $range, $allowEmpty = false, $message = '')
    {
        if ($allowEmpty) {
            $this->validator->allowEmpty($field);
        } else {
            $this->isNotEmpty($field);
        }

        if (empty($message)) {
            $humanField = Inflector::humanize($field);
            $message = __('{0} should be between {1} and {2}', $humanField, $range[0], $range[1]);
        }
        
        $this->validator->range($field, $range, $message);
        
        return $this;
    }
    
    
    /**
     * positive method
     * 
     * @param string $field
     * @param bool $allowZero
     * @param string $message
     * @return \CakeTools\Model\Validation\GlobalValidator
     */
    public function positive($field, $allowZero = false, $allowEmpty = false, $message = '') {
        $humanField = Inflector::humanize($field);
        
        if ($allowEmpty) {
            $this->validator->allowEmpty($field);
        } else {
            $this->isNotEmpty($field);
        }
        
        if (empty($message)) {
            $message = __('{0} should be a positive number', $humanField);
        }
        $this->validator->numeric($field, $message);
        
        if ($allowZero) {
            $this->validator->greaterThanOrEqual($field, 0, $message);
        } else {
            $this->validator->greaterThan($field, 0, $message);
        }
        
        return $this;
    }
    
    
    
    /** 
     * status method
     * 
     * @param string $field
     * @param string $class
     * @param bool $allowEmpty
     * @throws Exception
     * @return $this
     */
    public function status($class = null, $field = 'status', $allowEmpty = false)
    {
    	$humanField = Inflector::humanize($field);
    	
    	if ($allowEmpty) {
    	    $this->validator->allowEmpty($field);
    	} else {
    	    $this->isNotEmpty($field);
    	}

		$constants = [];
		// use the general class
		if ($class === true) {
		    $generalStatus = $this->_table->getAliasSetting('status_class'); 
			if (class_exists($generalStatus)) {
				$constants = getClassConstants($generalStatus, true);
			}
		} elseif (is_string($class)) {
			// if exact class is provided
			if (class_exists($class)) {
				$constants = getClassConstants($class, true);
			}
		}
		
		if (empty($constants)) {
		    throw new Exception('Status class does not exist');
		}

		$this->validator->add($field, [
			'validValue' => [
				'rule' => function ($value, $context) use ($constants) {
					if (!empty($constants[$value])) {
						return true;
					}

					return false;
				},
				'message' => __('Invalid value') 
			] 
		]);
		
		return $this;
    }
    
    
    /** 
     * slug methid
     * 
     * @param string $field
     * @param mixed $type - if boolean true (default) - English will be validated
     *                      if string starting by slash "/" or "#" - exact regex is considred 
     *                      if string - script name from this list is considered
     *                      @link http://php.net/manual/en/regexp.reference.unicode.php
     *                       
     * @return $this
     */
    public function slug($field = null, $type = true, $message = null)
    {
        if (empty($field)) {
            $field = 'slug';
        }
        
        $regex = '';
        if (is_bool($type)) {
            $regex = '/^[a-z\d-]{3,}$/';

            if (empty($message)) {
                $message = __('Only lowercase English characters, numbers and dash are allowed');
            }
        } elseif (is_string($type) && in_array(substr($type, 0, 1), ['/', '#'])) {
            $regex = $type;
            if (empty($message)) {
                $message = __('Invalid Value');
            }
        } elseif (is_string($type) && !empty($type)) {
            $regex = '/^[\p{'.$type.'}-\d]{3,}$/u';
            if (empty($message)) {
                $message = __('Only lowercase {0} characters, numbers and dash are allowed', $type);
            }
        } else {
            throw new Exception('Invalid argument "type": should be boolean or string');
        }

        $this->validator->add($field, [
            'validValue' => [
                'rule' => function ($value, $context) use($regex) {
                    if (preg_match($regex, $value)) {
                        return true;
                    }
                    
                    return false;
                },
                'message' => $message
            ]
        ]);
        
        return $this;
    }
    
    
    /** 
     * password method
     * 
     * @param string $field
     * @param number $minLength
     * @return $this
     */
    public function password($field = 'password1', $minLength = 8) {
        $this->isNotEmpty($field, __('Password can not be empty'));
        
        /**
         * https://www.owasp.org/index.php/Authentication_Cheat_Sheet#Password_Length
         */
        $maxLength = 128;

        $this->validator->minLength($field, $minLength, __('Should be at least {0} characters', $minLength));
        $this->validator->maxLength($field, $maxLength, __('Should be no more than {0} characters', $maxLength));

        $this->validator->add($field, 'validPassword', [
            'rule' => function ($value, $context) {
                // check english characters
                if (!preg_match("/^[ -~]+$/", $value)) {
                    return __('Only English letters, numbers and special characters are allowed');
                }

                $len = round(strlen($value)*0.7);
                if (preg_match('/([\d\w])\1{4,}/i', $value)) {
                    return __('Too many repeating characters are easy to guess');
                }
            
                $commonPasswordModel = TableRegistry::get('ZCommonPasswords');
                if (!empty($commonPasswordModel)) {
                    $conditions = [
                        'LOWER(ZCommonPasswords.password)' => strtolower($value)
                    ];
                
                    if ($commonPasswordModel->find()->where($conditions)->count()) {
                        return __('Common passwords are easy to guess, please try again');
                    }
                }

                return true;
            }
        ]);
        
        return $this;
    }
    
    
    /**
     * custom method
     * 
     * @param string $field
     * @param string $rule
     * @param string $message
     * @return \CakeTools\Model\Validation\GlobalValidator
     */
    public function custom($field, $rule = '', $message = '')
    {
        $data = [
            'rule' => $rule,
            'provider' => 'table'
        ];
        
        if (!empty($message)) {
            $data['message'] = $message;
        }

        $this->validator->add($field, [
            $rule => $data
        ]);
        
        return $this;
    }
    
    
    /** 
     * compareFields method
     * compares 2 fields be be equal
     * 
     * @param string $field1
     * @param string $field2
     * @return $this
     */
    public function compareFields($field1 = '', $field2 = '') {
        if (empty($field1) || empty($field2)) {
            throw new Exception('Both fields are required for comparison');
        }
        
        $this->validator->add($field1, 'comparePasswords', [
            'rule' => ['compareWith', $field2],
            'message' => __('Passwords should match')
        ]);
        
        return $this;
    }
    
    
    
}