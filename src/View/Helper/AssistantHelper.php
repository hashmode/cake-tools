<?php 
namespace CakeTools\View\Helper;

use Cake\View\Helper;
use Cake\View\View;
use Cake\Utility\Inflector;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Hash;

/**
 * Assistant Helper
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 */
class AssistantHelper extends Helper
{
	public $helpers = [
		'Html',
		'Form'
	];

    /**
     *
     * @var array
     */
    protected $fullConfig = [];
    
    /**
     *
     * @var array
     */
    protected $mainConfig = [];

    /**
     *
     * @var array
     */
    protected $urlConfig = [];
    
    /**
     *
     * @var array
     */
    protected $viewConfig = [];

    /**
     *
     * @var array
     */
    protected $textConfig = [];

    
    /**
     * {@inheritDoc}
     * @see \Cake\View\Helper::initialize()
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        
        $settings = Configure::read('CakeTools');
        $this->fullConfig = $settings;
        $this->mainConfig = $settings['config'];
        $this->textConfig = $settings['text'];
        $this->urlConfig = $settings['controller']['urls'];
        $this->viewConfig = $settings['view']['helper'];
    }

    /**
     * getConfig method
     *
     * @param string $path            
     * @return mixed - array or null
     */
    public function getConfig($path = null)
    {
        if ($path === null) {
            return $this->fullConfig;
        }
        
        return Hash::get($this->fullConfig, $path);
    }
    
    
    /**
     * url method
     * 
     * @param string $url
     * @return string
     */
    public function url($url = '')
    {
        return $this->mainConfig['router_path'].$url;
    }
    
	
	/**
	 * getLogoutUrl method
	 * 
	 * @return string
	 */
	public function getLogoutUrl()
	{
	    return $this->configData['router_path'].'/users/logout';
	}
	
	
	/** 
	 * link method
	 * 
	 * @param string $title
	 * @param string $url
	 * @param array $options
	 * @return \Cake\View\Helper\HtmlHelper
	 */
    public function link($title, $url, array $options = [])
    {
        if (isset($options['icon']) && $options['icon']) {
            $title = '<span class="glyphicon glyphicon-'.$options['icon'].' mr5" aria-hidden="true"></span>' . $title;
            $options['escape'] = false;
        }

        if (!empty($options['btn'])) {
            $options['escape'] = false;
            if (empty($options['class'])) {
                $options['class'] = '';
            }
            
            $options['class'] .= ' btn btn-' . (is_string($options['btn']) ? $options['btn'] : $this->viewConfig['html']['link_btn']);
        }
        
        if (isset($options['size']) && $options['size']) {
            if (empty($options['class'])) {
                $options['class'] = '';
            }
            
            $options['class'] .= ' btn-' . $options['size'];
        }
        
        return $this->Html->link($title, $url, $options);
    }

    
    /** 
     * submit method
     * 
     * @param string $title
     * @param array $options
     * @return \Cake\View\Helper\FormHelper
     */
    public function submit($title = '', array $options = [])
    {
        if (empty($title)) {
            $title = $this->textConfig['view']['submit'];
        }
        
        if (empty($options['class'])) {
            $options['class'] = '';
        }

        /** 
         * either btn is set and is not false 
         * or
         * btn is not set and class is empty
         * or
         * btn is not set and class is not empty
         */
        if (!empty($options['btn']) 
            || empty($options['class']) 
            || (!empty($options['class']) && !strstr($options['class'], 'btn'))) {

            $options['class'] .= ' btn btn-' . (!empty($options['btn']) ? $options['btn'] : $this->viewConfig['form']['submit_btn']);
        }
        
        if (!empty($options['block']) || !empty($this->viewConfig['form']['submit_block'])) {
            $options['class'] .= ' btn-block';
        }

        if (!isset($options['data-loading-text']) || (isset($options['data-loading-text']) && $options['data-loading-text'] != false)) {
            $options['data-loading-text'] = $this->textConfig['view']['submit_loading'];
        }
        
        $response = '';
        if (!isset($options['wrap']) || $options['wrap'] !== false) {
            $response .= '<div class="form-group">';
        }
        $response .= $this->Form->submit($title, $options);
        if (!isset($options['wrap']) || $options['wrap'] !== false) {
            $response .= '</div>';
        }

        return $response;
    }
    

    /**
     * input method
     * Form input wihout div
     * 
     * @param string $fieldName
     * @param array $options
     * @return string
     */
    public function input($fieldName, array $options = [])
    {
        if (empty($options['type'])) {
            return $this->Form->input($fieldName, $options);
        }

        if ($options['type'] == 'text') {
            $options['templates'] = [
                'inputContainer' => '{{content}}'
            ];
        } elseif ($options['type'] == 'checkbox') {
            $options['templates'] = [
                'checkboxContainer' => '{{content}}'
            ];
        } elseif ($options['type'] == 'checkbox') {
            $options['templates'] = [
                'radioContainer' => '{{content}}'
            ];
        }

        return $this->Form->input($fieldName, $options);
    }

    /**
     * select - method
     * sets defatuls parameters of select type
     *
     * @param string $name            
     * @param array $options            
     * @return \Cake\View\Helper\FormHelper
     */
    public function select($name, array $options = [])
    {
        $options['type'] = 'select';
        
        if (! isset($options['empty']) || ! is_string($options['empty'])) {
            $options['empty'] = $this->textConfig['view']['select_empty'];
        } elseif ($options['empty'] === false) {
            unset($options['empty']);
        }
        
        if (! isset($options['options']) || $options['options'] !== false || ($options['options'] && is_string($options['options']))) {
            if (isset($options['options']) && is_string($options['options'])) {
                $className = $options['options'];
            } else {
                $className = 'Const' . str_replace(" ", "", Inflector::humanize($name));
            }
            
            if (class_exists($className)) {
                $options['options'] = getClassConstants($className, true);
            }
        } elseif ($options['options'] === false) {
            unset($options['options']);
        }
        
        if (isset($options['div']) && $options['div'] === false) {
            $options['templates'] = [
                'inputContainer' => '{{content}}'
            ];
        }
        
        return $this->Form->input($name, $options);
    }

    /**
     * getYears method
     * 
     * @param number $from - year to start with
     * @param number $years
     */
    public function getYears($from = null, $years = 10)
    {
        if (! $from) {
            $from = date('Y');
        }
        
        $range = getRange($from, $from + $years);
        return array_combine($range, $range);
    }
    
    
    /**
     * checkboxList method
     * creates checkbox list based on data
     *
     * @param ArrayObject $data            
     * @param string $alias            
     * @param array $options            
     * @return string
     */
    public function checkboxList($data, $alias = '', $options = [])
    {
        $options = array_merge([
            'value' => 'id',
            'label' => 'title'
        ], $options);
        
        $response = $this->Form->hidden($alias . '[_ids]', [
            'value' => ''
        ]);
        
        foreach ($data as $d) {
            $response .= $this->Form->input($alias . '[_ids][]', [
                'type' => 'checkbox',
                'value' => $d->{$options['value']},
                'label' => $d->{$options['label']},
                'hiddenField' => false
            ]);
        }
        
        return $response;
    }
    
    
    /**
     * slug method
     *
     * @param string $title
     * @param string $url
     * @param array $options
     * @return string
     */
    public function slug($title, $url, array $options = [])
    {
        return $this->Html->link($title, Router::url($url), $options);
    }
    

    /**
     * disabledField method
     * creates the fields as disabled, adding hidden input to preserve the value as well
     *
     * @param string $name            
     * @param array $options            
     */
    public function disabledField($name, $options = [])
    {
        $options['disabled'] = 'disabled';
        $hiddenOptions = array_merge($options, [
            'type' => 'hidden'
        ]);
        unset($hiddenOptions['disabled']);
        return $this->Form->input($name, $options) . $this->Form->input($name, $hiddenOptions);
    }

    
    /**
     * loading method
     * 
     * @return string
     */
    public function loading()
    {
        return $this->Html->image('CakeTools.loading.gif');
    }

    /**
     * inflector method
     * wrapper for Cake\Utility\Inflector
     * 
     * @param string $method
     * @return string
     */
    public function inflector($method = '', $argument = '')
    {
        if (method_exists(new Inflector(), $method)) {
            return Inflector::$method($argument);
        }
        
        return '';
    }
    
    
    
    /**
     * ********************************** BROWSER METHODS ***************************
     */
    
    
    /**
     * browser method
     * 
     * @return string
     */
    public function browser()
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        // you can add different browsers with the same way ..
        if (preg_match('/(chromium)[ \/]([\w.]+)/', $ua)) {
            $browser = 'chrome';
        } elseif (preg_match('/(chrome)[ \/]([\w.]+)/', $ua)) {
            $browser = 'chrome';
        } elseif (preg_match('/(safari)[ \/]([\w.]+)/', $ua)) {
            $browser = 'safari';
        } elseif (preg_match('/(opera)[ \/]([\w.]+)/', $ua)) {
            $browser = 'opera';
        } elseif (preg_match('/(msie)[ \/]([\w.]+)/', $ua)) {
            $browser = 'msie';
        } elseif (preg_match('/(mozilla)[ \/]([\w.]+)/', $ua)) {
            $browser = 'mozilla'; // for mozilla it always returns version 5
            $br = $this->getBrowserAlt();
        }
        
        preg_match('/(' . $browser . ')[ \/]([\w]+)/', $ua, $version);
        
        if (isset($br)) {
            $browser = $br;
        }
        
        return $browser . " " . $browser . "_" . $version[2];
    }

    /**
     * getBrowserAlt method
     * 
     * @return string
     */
    private function getBrowserAlt()
    {
        $firefoxStart = strrpos($_SERVER['HTTP_USER_AGENT'], 'Firefox');
        $chromeStart = strrpos($_SERVER['HTTP_USER_AGENT'], 'Chrome');
        $safariStart = strrpos($_SERVER['HTTP_USER_AGENT'], 'Safari');
        
        if ($firefoxStart !== false) {
            $version = $this->getBrowserVersion($firefoxStart, 8);
            return "firefox_" . $version;
        } elseif ($chromeStart !== false) {
            $version = $this->getBrowserVersion($chromeStart, 7);
            return "chrm_" . $version;
        } elseif ($safariStart != false) {
            $version = $this->getBrowserVersion($safariStart, 7);
            return "sfr_" . $version;
        }
    }

    /**
     * getBrowserVersion method
     * 
     * @param string $start
     * @param string $length
     * @return mixed
     */
    private function getBrowserVersion($start, $length)
    {
        $end = strpos($_SERVER['HTTP_USER_AGENT'], ' ', $start);
        if ($end === false) {
            $end = strlen($_SERVER['HTTP_USER_AGENT']);
        }
        
        $versionStr = substr($_SERVER['HTTP_USER_AGENT'], $start + $length, $end - $start - $length);
        $version = floatval($versionStr);
        $version = str_replace('.', '', $version);
        
        return $version;
    }

    
    
    
    
    public function _url($url)
    {
        return $url;
    }

    public function _index($model = null)
    {
        return $model;
    }

    public function _add($model = null)
    {
        return $model . '/add';
    }

    public function _view($id = null, $model = null)
    {
        return $model . '/view/' . $id;
    }

    public function _edit($id = null, $model = null)
    {
        return $model . '/edit/' . $id;
    }

    public function _delete($id = null, $model = null)
    {
        return $this->viewConfig['url']['user']['delete'] . $model . '/' . $id;
    }

    public function _position($id = null, $model = null, $position)
    {
        return $this->urlConfig['user']['position'] . $model . '/' . $id . '/' . $position;
    }

    public function _status($id = null, $status = null, $model = null)
    {
        return $this->urlConfig['user']['status'] . $model . '/' . $id . '/' . $status;
    }

    
    /**
     * Admin actions
     */
    public function _admin_url($url)
    {
        return $this->urlConfig['admin']['prefix'] . '/' . $url;
    }

    public function _admin_index($model = null)
    {
        return $this->urlConfig['admin']['prefix'] . '/' . $model;
    }

    public function _admin_add($model = null)
    {
        return $this->urlConfig['admin']['prefix'] . '/' . $model . '/add';
    }

    public function _admin_view($id = null, $model = null)
    {
        return $this->urlConfig['admin']['prefix'] . '/' . $model . '/view/' . $id;
    }

    public function _admin_edit($id = null, $model = null)
    {
        return $this->urlConfig['admin']['prefix'] . '/' . $model . '/edit/' . $id;
    }

    public function _admin_delete($id = null, $model = null)
    {
        return $this->urlConfig['admin']['delete'] . $model . '/' . $id;
    }

    public function _admin_position($id = null, $model = null, $position)
    {
        return $this->urlConfig['admin']['position'] . $model . '/' . $id . '/' . $position;
    }

    public function _admin_status($id = null, $status = null, $model = null)
    {
        return $this->urlConfig['admin']['status'] . $model . '/' . $id . '/' . $status;
    }
	
	
	
	
}