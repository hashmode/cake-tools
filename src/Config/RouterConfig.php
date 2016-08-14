<?php 
namespace CakeTools\Config;

use Cake\ORM\TableRegistry;
use Cake\Core\Plugin;
use Cake\Utility\Inflector;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;

/**
 * RouterConfig
 */
class RouterConfig
{
    
    /** 
     * the urls' prefix (should be updated if the site is in the subdirectory)
     * @var string
     */
    protected static $prefix = '';
    
    /** 
     * regex to identify the url params
     *  
     * @var string
     */
    protected static $urlPattern = '/^[a-z\d-]{3,}$/i';

    
    /**
     * conditions for all rules
     * 
     * @var array
     */
    protected static $globalConditions = [];
    
    
    /**
     * getPrefix method
     * 
     * @param string $pattern
     * @return string
     */
    public static function getPrefix($prefix = '')
    {
        if (!empty($prefix) && is_string($prefix)) {
            self::$prefix = $prefix;
        }

        return self::$prefix;
    }
    
    /**
     * getUrlPattern method
     * 
     * @param string $pattern
     * @return string
     */
    public static function getUrlPattern($pattern = null)
    {
        // @TODO - check for valid regex ?
        if (!empty($pattern) && is_string($pattern)) {
            self::$urlPattern = $pattern;
        }

        return self::$urlPattern;
    }
    
    
    /**
     * getUrlPattern method
     * 
     * @param string $pattern
     * @return string
     */
    public static function getGlobalConditions($conditions = null)
    {
        if ($conditions !== null) {
            self::$globalConditions = $conditions;
        }

        return self::$globalConditions;
    }

    /**
     * getControllers method
     *
     * @return array - list of controllers from only Controller directory
     */
    public static function getControllers()
    {
        $files = scandir(APP . 'Controller');
        $results = [];
        foreach($files as $file){
            if (preg_match('/^[A-Za-z\d]+(?=(\.php)$)/', $file, $matches)) {
                if ($matches[0] !== 'AppController') {
                    $results[] = strtolower(str_replace('Controller', '', $matches[0]));
                }
            }
        }

        return $results;
    }

    /**
     * isReservedUrl method
     * checks if the current page is some reserved url, such as
     * - existing controllers
     * - plugins' name, path
     * - provided by Configure::write('CakeTools.config.reserved_paths', [ ... ])
     *
     * @return bool
     */
    public static function isReservedUrl()
    {
        $url = self::getSlug();
        
        if (!$url) {
            return true;
        }
        
        // get url's first param
        $firstParam = current(explode("/", $url));
        
        // get controllers
        $controllers = self::getControllers();
        
        // get loaded plugins
        $plugins = Plugin::loaded();
        $dashedPlugins = $plugins;
        
        // consider also the dashed versions of plugins' names
        array_walk($dashedPlugins, function (&$val) {
            $val = strtolower(Inflector::dasherize($val));
        });

        // make all plugin names' lowercase
        array_walk($plugins, function(&$val) {
            $val = strtolower($val);
        });

        $reservedPaths = Configure::read('CakeTools.config.reserved_paths');
        
        $reserved = array_merge($controllers, $dashedPlugins, $plugins, $reservedPaths);
        
        if (in_array($firstParam, $reserved)) {
            return true;
        }

        return false;
    }

    /**
     * getRouterOptions method
     *
     * @TODO
     * - make support multi-param urls e.g. /some-slug/another-slug/third-slug
     *
     * @param array $rules - list of rules(by the order they should be checked)
     * @return array|boolean
     */
    public static function getRouterOptions($rules = [])
    {
        // if this page is reserved - all good
        if (self::isReservedUrl()) {
            return false;
        }
        
        if (empty($rules) || !is_array($rules)) {
            return false;
        }

        $slugList = self::getSlugList();
        $slug = $slugList[0];

        $response = false;
        foreach ($rules as $rule) {
            // @TODO - validate $rule
            
            /**
             * double slug and contain option should exist together 
             */
            if (empty($rule['contain']) && !empty($slugList[1]) || !empty($rule['contain']) && empty($slugList[1])) {
                continue;
            }
            
            /**
             * double slug cases 
             */
            if (!empty($slugList[1])) {
                $childSlug = $slugList[1];

                // check if slug is valid
                if (preg_match(self::getUrlPattern(), $slug)) {
                    $parentId = self::processRule($rule, $slug);

                    if (!empty($parentId)) {
                        $childRule = $rule['contain'];
                        if (! isset($childRule['conditions'])) {
                            $childRule['conditions'] = [];
                        }

                        $childRule['conditions'][$childRule['foreign_key']] = $parentId;

                        $childId = self::processRule($childRule, $childSlug);
                        if ($childId) {
                            $controller = !empty($childRule['controller']) ? $childRule['controller'] : strtolower($childRule['alias']);
                            $response = self::getOptions($controller, $childRule['action'], $childId, $parentId);
                            break;
                        }
                    }
                }

            } else {
                /** 
                 * one slug case
                 */
                $id = self::processRule($rule, $slug);
                if ($id) {
                    $controller = !empty($rule['controller']) ? $rule['controller'] : strtolower($rule['alias']);
                    $response = self::getOptions($controller, $rule['action'], $id);
                    break;
                }
            }

        }   // loop END

        return $response;
    }
    
    
    /**
     * processRule method
     * 
     * @param array $rule
     * @param string $val
     * @return mixed|boolean
     */
    private static function processRule($rule = [], $val = '') {
        if (!empty($rule['hash'])) {
            if (is_string($rule['hash']) && in_array($rule['hash'], hash_algos())) {
                $val = hash($rule['hash'], $val);
            } else {
                $val = md5($val);
            }
        }
        
        $conditions = [
            'OR' => []
        ];
        
        if (is_string($rule['fields'])) {
            $rule['fields'] = [
                $rule['fields']
            ];
        }
        
        // set conditions
        foreach ($rule['fields'] as $field) {
            $conditions['OR'][] = [
                $rule['alias'] . '.' . $field => $val
            ];
        }
        
        if (!empty($rule['conditions'])) {
            $conditions = array_merge($conditions, $rule['conditions']);
        }
        
        if (!empty(self::$globalConditions)) {
            $conditions = array_merge($conditions, self::$globalConditions);
        }
        return self::checkModel($rule['alias'], $conditions);
    }

    /**
     * getOptions method
     * returns the options for `$routes->connect` method
     *
     * @param string $controller            
     * @param string $action            
     * @param string $_params            
     * @return array
     */
    private static function getOptions()
    {
        $params = func_get_args();
        if (count($params) <= 2) {
            throw new Exception('getRoutingOptions method requires at least 2 parameters');
        }
        
        $arr = [];
        $i = 1;
        foreach ($params as $param) {
            if ($i == 1) {
                $arr['controller'] = $param;
            } elseif ($i == 2) {
                $arr['action'] = $param;
            } else {
                $arr[] = $param;
            }
            
            $i++;
        }

        return $arr;
    }

    /**
     * checkModel method
     * checks if the given slug belongs to the current model
     *
     * @param string $alias            
     * @param array $conditions            
     * @return mixed - the model's id if found, boolean false otherwise
     */
    private static function checkModel($alias, $conditions = [])
    {
        $model = TableRegistry::get($alias);
        
        $data = $model->find()
            ->select([
            $alias . '.id'
        ])
            ->where($conditions)
            ->first();

        // important
        TableRegistry::clear();

        if (!empty($data)) {
            return $data->id;
        }

        return false;
    }

    /**
     * getUrl method
     * returns the current url - decoded, all lowercase and trimmed the trailing slash
     *
     * @param bool $exact
     * @return string
     */
    public static function getUrl($exact = false)
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = urldecode($_SERVER['REQUEST_URI']);
            
            $prefix = self::$prefix;
            if ($prefix) {
                if (mb_substr($url, 0, mb_strlen($prefix)) == $prefix) {
                    $url = mb_substr($url, mb_strlen($prefix));
                }
            }
            
            if ($exact) {
                return $url;
            }
            
            return mb_strtolower(trim($url));
        }
        
        return false;
    }
    
    /**
     * getSlug method
     * 
     * @param bool $exact
     * @return string
     */
    public static function getSlug($exact = false)
    {
        return trim(self::getUrl($exact), "/");
    }
    
    /**
     * getSlugList method
     * 
     * @param bool $exact
     * @return array
     */
    public static function getSlugList($exact = false)
    {
        return explode("/", trim(self::getUrl($exact), "/"));
    }

}
