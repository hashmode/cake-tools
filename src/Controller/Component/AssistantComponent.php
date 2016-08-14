<?php
namespace CakeTools\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Exception\Exception;
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Assistant Component
 */
class AssistantComponent extends Component
{

    /**
     *
     * @property \CakeTools\Controller\AppController
     */
    public $controller = null;

    /**
     * {@inheritDoc}
     * @see \Cake\Controller\Component::initialize()
     */
    public function initialize(array $config) {
        parent::initialize($config);
    
        $this->controller = $this->_registry->getController();
    }

    /**
     * getModelList method
     *
     * @param string $alias            
     * @param bool $active            
     * @param array $conditions            
     * @return array - in id => name format
     */
    public function getModelList($alias = null, $active = true, array $conditions = [])
    {
        $model = TableRegistry::get($alias);
        $options = [];

        $statusField = $this->getAliasSetting($alias, 'status_field');
        if (! $statusField) {
            $statusField = 'status';
        }
        
        $options['conditions'] = [];
        if ($statusField && $active && $model->hasField($statusField)) {
            $statusClass = $this->getAliasSetting($alias, 'status_class');
            $statusClassActive = $this->getAliasSetting($alias, 'status_class_active');
            
            if ($statusClass && $statusClassActive) {
                if (! class_exists($statusClass) || ! defined("$statusClass::$statusClassActive")) {
                    $m = 'Status class %s does not exist or its active constants %s is not defined';
                    throw new Exception(sprintf($m, $statusClass, $statusClassActive));
                }

                $options['conditions'] = [
                    $statusField => constant("$statusClass::$statusClassActive")
                ];
            }
        }
        
        if (! empty($conditions)) {
            $options['conditions'] = array_merge($options['conditions'], $conditions);
		}

		return $model->find('list', $options)->toArray();
	}

    /**
     * getAliasSetting method
     *
     * @param string $alias
     * @param string $key
     * @param string $forceModel
     * @return mixed|\ArrayAccess|array[]|\ArrayAccess[]|null
     */
    private function getAliasSetting($alias, $key = null, $forceModel = false)
    {
        $value = Configure::read('CakeTools.settings.status.models.' . $alias . '.' . $key);
        if ($value) {
            return $value;
        }
        
        return Configure::read('CakeTools.settings.status.' . $key);
    }
	
	
	/** 
	 * getModelFullList method
	 * 
	 * @param string $alias
	 * @param string $active
	 * @param array $conditions
	 * @param array $fields
	 * @return array
	 */
	public function getModelFullList($alias = null, $active = true, array $conditions = [], array $fields = [])
	{
		$model = TableRegistry::get($alias);
		$options = [];
		
		$statusField = $this->getAliasSetting($alias, 'status_field');
		if (! $statusField) {
		    $statusField = 'status';
		}
		
		$options['conditions'] = [];
		if ($active && $statusField && $model->hasField($statusField)) {
		    $statusClass = $this->getAliasSetting($alias, 'status_class');
		    $statusClassActive = $this->getAliasSetting($alias, 'status_class_active');
		    
		    if ($statusClass && $statusClassActive) {
		        if (! class_exists($statusClass) || ! defined("$statusClass::$statusClassActive")) {
		            $m = 'Status class %s does not exist or its active constants %s is not defined';
		            throw new Exception(sprintf($m, $statusClass, $statusClassActive));
		        }
		    
		        $options['conditions'] = [
		            $statusField => constant("$statusClass::$statusClassActive")
		        ];
		    }
		}
		
		if (!empty($conditions)) {
			$options['conditions'] = array_merge($options['conditions'], $conditions);
		}
		
		$options['fields'] = [
		    'id',
		    $model->displayField()
		];

		if (!empty($fields)) {
			$options['fields'] = array_merge($options['fields'], $fields);
		}

		return $model->find('all', $options)->toArray();
	}
	
	
	/**
	 * getModelOptionList method
	 *
	 * @param string $alias
	 * @return array - with model id/name
	 */
	public function getModelOptionList($alias = null, $active = true, $conditions = [])
	{
	    $_select = Configure::read('CakeTools.text.view.select_text');
	    $response = '<option value="">'.$_select.'</option>';
	
	    if (!$alias) {
	        return $response;
	    }
	
	    $data = $this->getModelList($alias, $active, $conditions);
	    foreach ($data as $id => $name) {
	        $response .= '<option value="'.$id.'">'.$name.'</option>';
	    }

	    return $response;
	}


    /**
     * getValidationErrors method
     * returns the validation errors after newEntities/patchEntities
     * 
     * prefix is necesssary if mixed (new items and updating items) are provided to not conflict id with key
     *
     * @param array $entities            
     * @return array
     */
    public function getValidationErrors($entities, $prefix = 'i')
    {
        $errors = [];
        
        $i = 0;
        foreach ($entities as $entity) {
            $k = !empty($entity->id) ? $entity->id : $prefix.$i;
            $errors[$k] = $entity->errors();
            
            $i++;
        }
        
        $filtered = array_filter($errors);
	    
	    return empty($filtered) ? [] : $errors;
	}
	
    /**
     * checkSlugCase method
     * checks if current url's case and slug's case are the same
     *
     * @param string $slug            
     */
    public function checkSlugCase($slug = '')
    {
        if (! empty($slug)) {
            $url = $this->controller->request->url;
            
            if ($url !== $slug) {
                return $this->controller->redirect('/' . $slug, 301);
            }
        }
    }

    /**
     * isActive method
     * check if the given record is active
     *
     * @param string $id            
     * @param string $alias            
     * @param number $status            
     * @return bool
     */
    public function isActive($id = null, $alias = null)
    {
        $statusField = $this->getAliasSetting($alias, 'status_field');
        if (! $statusField) {
            $statusField = 'status';
        }
        
        $statusClass = $this->getAliasSetting($alias, 'status_class');
        $statusClassActive = $this->getAliasSetting($alias, 'status_class_active');
        
        $model = TableRegistry::get($alias);
        if (! $model->hasField($statusField) || ! class_exists($statusClass) || ! defined("$statusClass::$statusClassActive")) {
            throw new Exception('Status field does not exist or class is not defined');
        }
        
        $query = $model->find();
        $query->where([
            'id' => $id,
            $statusField => constant("$statusClass::$statusClassActive")
        ]);
        
        return $query->count() > 0 ? true : false;
    }
    
    
    
    /**
     * redirect method
     * dirty way to emulate cakephp 2.x compatible redirect
     * 
     * ******** NOT recommended *********
     * 
     * @param string $url
     * @param number $status
     * @link http://stackoverflow.com/a/32190873/932473
     */
    public function redirect($url, $status = 301)
    {
        $this->controller->response = $this->controller->redirect($url, $status);
        $this->controller->response->send();
        die;
    }
    
	
	
}