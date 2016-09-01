<?php
use Cake\Core\Configure;
use Cake\Cache\Cache;

/** 
 * configure cache to be used from CLI - for __ function
 */
if (empty(Cache::configured())) {
    Cache::config(Configure::consume('Cache'));
}

$defaultPath = 'services';
if (!empty(Configure::read('CakeTools.config.router_path'))) {
    $defaultPath = Configure::read('CakeTools.config.router_path');
}

$defaultConfig = [
    'config' => [
        'bake_functions' => true,
        'router_path' => $defaultPath,
        'reserved_paths' => [
            'webroot',
            'css',
            'img',
            'js'
        ]
    ],
    'settings' => [
        'server_timezone' => 'UTC',
        'user_timezone' => 'Europe/Paris',
        'shorten_length' => 50,
        'status' => [
            'status_field' => 'status',
            'status_class' => 'ConstGeneralStatus',
            'status_class_active' => 'Active',
            'models' => []
        ],
    ],
    'model' => [
        'validation' => [
            'length' => [
                'varchar' => 300,
                'text' => 65000,
            ]
        ],
    ],
    'text' => [
        'view' => [
            'submit' => __('Submit'),
            'submit_loading' => __('Please Wait...'),
            'select_empty' => __('--Select--'),
            'select_loading' => __('Loading...'),
            'actions_dropdown' => __('Actions')
        ]
    ],
    'controller' => [
        'urls' => [
            'admin' => [
                'prefix' => '/admin',
                'delete' => '/'.$defaultPath.'/admins/delete/',
                'position' => '/'.$defaultPath.'/admins/position/',
                'status' => '/'.$defaultPath.'/admins/editStatus/'
            ],
            'user' => [
                'prefix' => '',
                'delete' => '/'.$defaultPath.'/users/delete/',
                'position' => '/'.$defaultPath.'/users/position/',
                'status' => '/'.$defaultPath.'/users/editStatus/'
            ]
        ]
    ],
    'view' => [
        'helper' => [
            'form' => [
                'submit_btn' => 'default',
                'submit_block' => true
            ],
            'html' => [
                'action_btn' => 'default',
                'action_btn_size' => 'xs',
                'link_btn' => 'default',
                'link_block' => false
            ]
        ]
    ],
    'security' => [
        /**
         * @array
         */
        'password_reset' => [
            /**
             * the db field where hash should be saved 
             */
            'hash_field' => 'password_reset_hash',
            /** 
             * the db field for reset request time 
             */
            'date_field' => 'password_reset_requested',
            /**
             * count - time after which password reset link will expire
             * e.g.
             * 15 minutes, 1 hour, 5 hours, 1 month
             */
            'time' => '15 minutes'
        ],
        
        /**
         * @mixed
         */
        'login_captcha' => [
            /** 
             * field in db
             * - boolean true - always show (field in db not required)
             * - boolean false - never show
             * - array with params 
             */
            'field' => 'failed_login_count',
            /**
             * after how many failed login attempts show the captcha 
             */
            'limit' => 3,
            /** 
             * number of letters to show on captcha image
             */
            'count' => 5
        ],
        
        /**
         * bcrypt's cost
         * @int
         */
        'bcrypt_cost' => 12,
        
        /**
         * key for password hash's AES256 encryption
         * @string
         */
        'password_key' => '',
        
        /**
         * used for encryption in other cases
         * @string
         */
        'app_key' => '',
    ],
    'bake_config' => [
        'buttonClass' => '',
        'buttonType' => '',
        'formClass' => '',
        'tableClass' => 'table table-striped table-bordered table-condensed',
        'actionsElement' => 'CakeTools.actions',
        'paginationElement' => 'CakeTools.pagination',
        'textMaxLength' => 65000,
        'tinymceMin' => 'tinymce/tinymce.min.js',
        'tinymceInit' => 'tinymce_init.js',
        /**
         * importance order
        *
        * 1) include for the given Model is provided
        * just consider model include + global include - model exclude
        * - can be used, when some fields are the same for all models,
        * while specific model has specific fields. Also, it might be that
        * some of the general fields should be excluded for this model
        *
        * 2) model include not provided, but there is model exclude
        * consider global exclude + model exclude
        * - can be used when only some general fields, and fields specific
        *   to this model that should be excluded
        *
        * 3) no model data
        *    if provided use global include
        * - can be used when for all models specific list of fields is fine
        *
        * 4) no model data
        *    use global exclude if any
        * - can be used if all fields are ok except a few
        *
        */
        'fields' => [
            'model' => [
                'include' => [
                ],
                'exclude' => [
                    
                ]
            ],
            'form' => [
                'include' => [
        
                ],
                'exclude' => [
                ],
                'tinymce' => [
                    
                ],
            ],
            'view' => [
                'include' => [
                ],
                'exclude' => [
                ],
            ],
            'index' => [
                'listCheckbox' => false,
                'include' => [
                ],
                'exclude' => [
                ],
                /**
                 * 'add' => true,
                 * 'view' => true,
                 * 'edit' => true,
                 * 'delete' => true
                 * 'order' => true
                 */
                'actions' => [
                ]
            ],
            'models' => [
        
            ]
        ]
    ]
];


/** 
 * set user configuration
 */
$userConfig = Configure::read('CakeTools');
if (!empty($userConfig) && is_array($userConfig)) {
    $defaultConfig = array_replace_recursive($defaultConfig, $userConfig);
}

if (!$defaultConfig['config']['bake_functions']) {
    unset($defaultConfig['bake_config']);
}

Configure::write('CakeTools', $defaultConfig);

/** 
 * url path and directory name where plugin is installed
 * @var string
 */
if (!defined('CT_ROUTER_PATH')) {
    define('CT_ROUTER_PATH', $defaultPath);
}
if (!defined('CT_ROUTER_NAME')) {
    define('CT_ROUTER_NAME', 'cake_tools');
}

require_once 'core.php';
require_once 'events.php';
