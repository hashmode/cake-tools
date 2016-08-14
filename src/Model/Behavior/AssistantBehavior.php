<?php
namespace CakeTools\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Core\Exception\Exception;
use Cake\Utility\Inflector;
use Cake\Database\Query;
use Cake\Datasource\ConnectionManager;
use Cake\Core\Configure;

/** 
 * Assistant Behavior
 * 
 * @property \Cake\ORM\Table $event->subject()
 */
class AssistantBehavior extends Behavior
{

    /** 
     * config for creating slug
     * @var array
     */
    protected $_slugConfig = [
        'field' => 'title',
        'slug' => 'slug',
        'slug_old' => 'slug_old',
        'slug_last_modified' => 'slug_last_modified',
        'created' => 'created',
        'replacement' => '-',
        'translit' => false,
        'update_time' => DAY
    ];

    /**
     * config for db order
     * 
     * @var array
     */
    protected $_orderConfig = [
        'position' => 'position',
        'is_parent_null' => true,
        'fields' => [
            'parent_id'
        ]
    ];
    
    /** 
     * config for soft delete
     * 
     * @var array
     */
    protected $_deleteConfig = [
        'date_field' => 'deleted',
        'user_field' => 'delete_user_id'
    ];
    
    /** 
     * list of ids that were inserted during save operation
     * (for new records only)
     * 
     * @var array
     */
    protected $_insertedIds = [];
    
    /** 
     * list of ids that were saved during save operation
     * (for new and updating records)
     * 
     * @var array
     */
    protected $_savedIds = [];
    
    
    
    /**
     * @see \Cake\ORM\Behavior::initialize()
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        
    }

    /**
     * setPosition method
     * checks if position field is set - assigns incremented number
     *
     * @param Event $event            
     * @param Entity $entity            
     * @param array $orderConfig
     * @throws Exception - if invalid fields are provided
     * @return void
     */
    public function setPosition(Event $event, Entity $entity, $orderConfig = [])
    {
        /* @var $eventSubject \Cake\ORM\Table */
        $eventSubject = $event->subject();
        $alias = $eventSubject->alias();

        $config = $this->getOrderConfig($eventSubject, $orderConfig);
        if ($config === true) {
            return true;
        }

        // only when creating || if position already is set
        if (!$entity->isNew() || !empty($entity->{$config['position']})) {
            return true;
        }
        
        $findOptions = [];
        foreach ($config['fields'] as $field) {
            if (isset($entity->{$field})) {
                $findOptions['conditions'][$alias . '.' . $field] = $entity->{$field};
            } else {
                if ($config['is_parent_null']) {
                    $findOptions['conditions'][$alias . '.' . $field.' IS'] = null;
                } else {
                    $findOptions['conditions'][$alias . '.' . $field] = 0;
                }
            }
        }
        
        $query = $eventSubject->find('all', $findOptions);
        $item = $query->select([
            'max' => $query->func()
                ->max($config['position'])
        ])->toArray();
        
        $entity->{$config['position']} = ! empty($item[0]['max']) ? $item[0]['max'] + 1 : 1;
    }

    /**
     * setOrder method
     * if position field exits - applies order condition
     *
     * @param Event $event            
     * @param Query $query            
     * @param array $orderConfig            
     * @return void
     */
    public function setOrder(Event $event, Query $query, array $orderConfig = [])
    {
        $config = $this->getOrderConfig($event->subject(), $orderConfig);
        
        if ($config === true) {
            return true;
        }
        
        $query->orderAsc($event->subject()->alias() . '.' . $config['position']);
    }
    
    /**
     * getOrderConfig method
     * 
     * @param Event $event
     * @param array $orderConfig
     * @throws Exception - if invalid fields are provided
     * @return bool|array - boolean true if order config disabled or required fields are not found
     *                          config array otherwise
     */
    private function getOrderConfig($subject, array $orderConfig = [])
    {
        $alias = $subject->alias();
        
        // if explicitely set to false -> do not apply
        if (isset($subject->orderConfig) && $subject->orderConfig === false) {
            return true;
        }
        
        $config = array_merge($this->_orderConfig, $orderConfig);
        
        // model's settings should overwrite the function settings
        if (!empty($subject->orderConfig)) {
            $config = array_merge($config, $subject->orderConfig);
        }
        
        // check if position field is valid
        if (!$subject->hasField($config['position'])) {
            if ($config['position'] != $this->_orderConfig['position']) {
                throw new Exception(sprintf('Field %s does not exist for model %s', $config['position'], $alias));
            } else {
                return true;
            }
        }
        
        // check if condition fields are valid
        if (!empty($config['fields'])) {
            if (is_string($config['fields'])) {
                $config['fields'] = [
                    $config['fields']
                ];
            }
            
            foreach ($config['fields'] as $k => &$field) {
                if (!$subject->hasField($field)) {
                    // if this is the parent_id - just unset
                    if ($field == 'parent_id') {
                        $field = null;
                    } else {
                        throw new Exception(sprintf('Field %s does not exist for model %s', $field, $alias));
                    }
                }
            }
            unset($field);

            $config['fields'] = array_filter($config['fields']);
        }
        
        return $config;
    }


    /** 
     * setSlug method
     * sets the slug based on given field
     * 
     * @param Event $event
     * @param Entity $entity
     * @param array $slugConfig
     * @throws Exception - if invalid fields are set
     * @return void
     */
    public function setSlug(Event $event, Entity $entity, $slugConfig = [])
    {
        $alias = $event->subject()->alias();
        
        // if slug is explicitely set to false -> do not apply
        if (isset($event->subject()->slugConfig) && $event->subject()->slugConfig === false) {
            return true;
        }

        $config = array_merge($this->_slugConfig, $slugConfig);

        // model's settings should overwrite the function settings
        if (!empty($event->subject()->slugConfig)) {
            $config = array_merge($config, $event->subject()->slugConfig);
        }
        
        $stop = false;
        // check if fields are valid
        foreach ($config as $key => $field) {
            if (in_array($key, ['field', 'slug', 'slug_old', 'slug_last_modified', 'created'])) {
                if (!$event->subject()->hasField($field)) {
                    // field does not exist, yet it was manually set
                    if ($field != $this->_slugConfig[$key]) {
                        throw new Exception(sprintf('Field %s does not exist for model %s', $field, $alias));
                    } elseif ($key == 'slug' || $key == 'field') {
                        // if required fields do not exist - no need to continue
                        $stop = true;
                        break;
                    }
                }
            }
        }

        if ($stop) {
            return true;
        }

        // check if entity's slug field is even exists in the entity
        if (empty($entity->{$config['slug']})) {
            return true;
        }

        $entity->{$config['slug']} = Inflector::slug($entity->{$config['field']}, $config['replacement']);
        
        if ($config['translit'] && !preg_match('/^[ -~]+$/', $entity->{$config['slug']})) {
            $trans = is_string($config['translit']) ? $config['translit'] : 'Any-Latin;Latin-ASCII;'; 
            $entity->{$config['slug']} = transliterator_transliterate($trans, $entity->{$config['slug']});
        }
        
        // only when updating
        if (!empty($entity->id) 
            && $event->subject()->hasField($config['slug_old']) 
            && $event->subject()->hasField($config['slug_last_modified'])
            && $event->subject()->hasField($config['created'])) {
                
            if (!empty($entity->{$config['slug']})) {
                
                $data = $event->subject()
                    ->find('all')
                    ->where([
                    $alias . '.id' => $entity->id
                ])
                    ->select([
                    $alias . '.' . $config['slug'],
                    $alias . '.' . $config['slug_last_modified'],
                    $alias . '.' . $config['created']
                ])->first();

                $lastModified = $data->{$config['slug_last_modified']} ? $data->{$config['slug_last_modified']} : $data->{$config['created']};
                
                // if more than $config['update_time'] have passed from the last slug change - register changes
                if (time() - strtotime($lastModified) > $config['update_time']) {
                    $entity->{$config['slug_last_modified']} = date('Y-m-d H:i:s');
                    $entity->{$config['slug_old']} = $data->{$config['slug']};
                }
            }
        }
    }
    
    
    /**
     * move method
     * change the position moving up or down
     *
     * @param int $id
     * @param int $type - if positive number - will be moved up, otherwise down
     * @param array $orderConfig - config for ordering
     * @return bool - if the item successfully moved, false otherwise
     */
    public function move($id, $type, $orderConfig = [])
    {
        $alias = $this->_table->alias();
        $config = $this->getOrderConfig($this->_table, $orderConfig);
        
        $fields = [
            $alias . '.id',
            $alias . '.' . $config['position']
        ];
        
        foreach ($config['fields'] as $parentField) {
            $fields[] = $alias . '.' . $parentField;
        }
        
        $thisItem = $this->_table->find('all', ['fields' => $fields])->where([$alias . '.id' => $id])->first();
        
        if (empty($thisItem)) {
            return false;
        }

        $findOptions = [];
        foreach ($config['fields'] as $parentField) {
            if ($thisItem->{$parentField}) {
                $findOptions['conditions'][$alias . '.' . $parentField] = $thisItem->{$parentField};
            } else {
                if ($config['is_parent_null']) {
                    $findOptions['conditions'][] = $alias . '.' . $parentField. ' IS NULL';
                } else {
                    $findOptions['conditions'][$alias . '.' . $parentField] = 0;
                }
            }
        }

        $this->_table->displayField($config['position']);
        $items = $this->_table->find('list', $findOptions)->toArray();
        
        // at least 2 items should exist to be possible to move
        if (count($items) <= 1) {
            return false;
        }

        $newPositions = [];

        $nextKey = false;
        $prevKey = false;
        foreach ($items as $thisId => $position) {
            if ($type > 0) {
                if ($thisId == $id) {
                    if ($prevKey) {
                        $thisPosition = $items[$thisId];
                        $items[$thisId] = $items[$prevKey];
                        $items[$prevKey] = $thisPosition;
                        $newPositions = [
                            [
                                'id' => $thisId,
                                $config['position'] => $items[$thisId]
                            ],
                            [
                                'id' => $prevKey,
                                $config['position'] => $items[$prevKey]
                            ]
                        ];
        
                        break;
                    }
                }
            } else {
                if ($thisId == $id) {
                    $nextKey = true;
                } elseif ($nextKey) {
                    $thisPosition = $items[$thisId];
                    $items[$thisId] = $items[$id];
                    $items[$id] = $thisPosition;
                    
                    $newPositions = [
                        [
                            'id' => $thisId,
                            $config['position'] => $items[$thisId]
                        ],
                        [
                            'id' => $id,
                            $config['position'] => $items[$id]
                        ]
                    ];

                    break;
                }
            }
            
            $prevKey = $thisId;
        }
        
        if (!empty($newPositions) && $this->saveMulti($newPositions, [
            'check' => false
        ])) {
            return true;
        }
        
        return false;
    }

    
    /**
     *  saveOne method
     *  cakephp2 save equivalent
     * 
     * @param array $data
     * @param array $entityOptions - newEntity's options and 'check' => true/false, 
     *                         if true(default) it will not check if record exists when primary key provided
     * @param array $saveOptions
     * @return \Cake\Datasource\EntityInterface|boolean
     */
    public function saveOne(array $data, array $entityOptions = [], array $saveOptions = [])
    {
        if (!isset($entityOptions['validate'])) {
            $entityOptions['validate'] = false;
        }
        
        $options = $entityOptions;
        unset($entityOptions['check']);

        $entity = $this->_table->newEntity($data, $entityOptions);
        
        $primaryKey = $this->_table->primaryKey();
        if (!empty($data[$primaryKey])) {
            $entity->{$primaryKey} = $data[$primaryKey];

            // if primary key is set and 'check' is disabled 
            if (isset($options['check']) && $options['check'] === false) {
                // if id field is set and isNew => false, it will not check if record exists
                $entity->isNew(false);
            }
        }

        return $this->_table->save($entity, $saveOptions);
    }
    
    
    
    /** 
     * saveMulti method
     * cakephp 2.x saveMany equivalent 
     * 
     * *** WARNING *** validation default is false
     * 
     * @param array $data
     * @param array $entityOptions
     * @param array $options
     * @return bool
     */
    public function saveMulti(array $data, array $entityOptions = [], $options = [])
    {
        if (empty($data)) {
            return false;
        }
        
        if (!isset($options['atomic'])) {
            $options['atomic'] = false;
        }
        
        if (!isset($entityOptions['validate'])) {
            $entityOptions['validate'] = false;
        }
        
        $response = false;
        $this->_table->connection()->transactional(function() use($data, &$response, $entityOptions, $options) {
            foreach ($data as $d) {
                $saved = $this->saveOne($d, $entityOptions, $options);
                if ($saved) {
                    $response[] = $saved;
                } else {
                    $response = false;
                    break;
                }
            }
        });

        return $response;
    }
    
    
    /** 
     * getModels method
     * 
     * @return array - with list of application Models
     */
    public function getModels()
    {
        $models = ConnectionManager::get('default')->schemaCollection()->listTables();
        array_walk($models, function(&$val) {
            $val = Inflector::camelize($val);
        });
        
        return $models;
    }
    
    
    /** 
     * getNestedList method
     * 
     * @param bool $active
     * @param array|string $fields
     * @return array
     */
    public function getNestedList($active = false, $fields = [])
    {
        $alias = $this->_table->alias();
        $model = $this->_table;
    
        $displayField = $model->displayField();
        $childAlias = 'Child'.$alias;
    
        $query = $model->find()
            ->select([
            $alias . '.id',
            $alias . '.parent_id',
            $alias . '.' . $displayField
        ]);

        $childFields = [
            $childAlias . '.id',
            $childAlias . '.parent_id',
            $childAlias . '.' . $displayField
        ];
            
        $config = $this->getOrderConfig($this->_table);
        if ($config['is_parent_null']) {
            $query->where([$alias.'.parent_id IS' => null]);
        } else {
            $query->where([$alias.'.parent_id' => 0]);
        }
        
        /**
         * consider status field
         */
        if ($active) {
            $statusField = $this->getAliasSetting('status_field');
            $statusClass = $this->getAliasSetting('status_class');
            $statusClassActive = $this->getAliasSetting('status_class_active');
            
            if (! class_exists($statusClass) || ! defined("$statusClass::$statusClassActive")) {
                $m = 'Status class %s does not exist or its active constant %s is not defined';
                throw new Exception(sprintf($m, $statusClass, $statusClassActive));
            }
            $query->where([
                $alias . '.' . $statusField => constant("$statusClass::$statusClassActive")
            ]);
            $query->contain([
                $childAlias => [
                    'conditions' => [
                        $childAlias . '.' . $statusField => constant("$statusClass::$statusClassActive")
                    ]
                ]
            ]);
        }
        
        if (!empty($fields)) {
            if (is_string($fields)) {
                $fields = [
                    $fields
                ];
            }
            
            $query->select($this->getAliasFields($fields));
            $childFields = array_merge($childFields, $this->getAliasFields($fields, $childAlias));
        }

        $query->contain([
            $childAlias => [
                'fields' => $childFields
            ]
        ]);

        return $query->all()->toArray();
    }
    
    /**
     * insert method
     * 
     * @param array $data
     * @return bool
     */
    public function insert(array $data = [])
    {
        if (empty($data)) {
            return false;
        }

        return $this->insertMulti([$data]);
    }
    
    /** 
     * insertMulti method
     * 
     * @param array $data
     * @return bool
     * @link http://api.cakephp.org/2.8/class-DboSource.html#_insertMulti
     */
    public function insertMulti(array $data = [])
    {
        if (empty($data)) {
            return false;
        }

        $fields = array_keys($data[0]);
        array_walk($fields, function(&$val) {
            $val = '`'.$val.'`';
        });
        unset($val);
        
        $query = $this->_table->query();
        $query->insert($fields);
    
        foreach ($data as $d) {
            $query->values($d);
        }

        return $query->execute()->rowCount() > 0 ? true : false;
    }
    
    
    /**
     * insertedIds method
     * if $id is provided - adds into $_insertedIds list, otherwise returns it
     *
     * @param int $id            
     * @return array
     */
    public function insertedIds($id = null)
    {
        $alias = $this->_table->alias();
        
        if ($id === null) {
            return !empty($this->_insertedIds[$alias]) ? $this->_insertedIds[$alias] : [];
        }
        
        $this->_insertedIds[$alias][] = $id;
        return $this->_insertedIds[$alias];
    }


    /**
     * savedIds method
     * if $id is provided - adds into $_savedIds list, otherwise returns it
     *
     * @param int $id            
     * @return array
     */
    public function savedIds($id = null)
    {
        $alias = $this->_table->alias();
        
        if ($id === null) {
            return !empty($this->_savedIds[$alias]) ? $this->_savedIds[$alias] : [];
        }
        
        $this->_savedIds[$alias][] = $id;
        return $this->_savedIds[$alias];
    }
    
    
    /**
     * getLastInsertId method
     * returns the last inserted id for this model
     * 
     * @return int
     */
    public function getLastInsertId()
    {
        return getLastValue($this->_insertedIds[$this->_table->alias()]);
    }    
    
    
    
    /**
     * sqlQuery method
     * executes raw sql query
     * 
     * @param string $sql
     */
    public function sqlQuery($sql)
    {
        /* @var $conn \Cake\Database\Connection  */
        $conn = ConnectionManager::get('default');
        
        try {
            return $conn->query($sql);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    
    /**
     * findLst method
     * find('list') similar to Cake 2.x - based on provided fields
     * 
     * @param Query $query
     * @param array $options
     */
    public function findLst(Query $query, array $options)
    {
        return $query->find('list', ['keyField' => $options[0], 'valueField' => $options[1]]);
    }
    
    
    /**
     * getAliasFields method
     * 
     * @param array $fields
     * @return array
     */
    public function getAliasFields($fields = [], $alias = null)
    {
        $alias = !empty($alias) ? $alias : $this->_table->alias();
        array_walk_recursive($fields, function (&$val) use ($alias) {
            $val = $alias.'.'.$val;
        });

        return $fields;
    }

    /**
     * getColumnTypes method
     * 
     * @return array - in "column name" => "column type" format
     */
    public function getColumnTypes()
    {
        $columns = $this->_table->schema()->columns();
        
        $columnTypes = [];
        foreach ($columns as $column) {
            $columnTypes[$column] = $this->_table->schema()->columnType($column);
        }
        
        return $columnTypes;
    }

    /**
     * getAliasSetting method
     * 
     * @param string $key
     * @param string $forceModel
     * @return mixed
     * 
     * 
     * * @TODO - AssistantComponent -> merge ?
     */
    public function getAliasSetting($key = null, $forceModel = false)
    {
        $value = Configure::read('CakeTools.settings.status.models.' . $this->_table->alias(). '.' . $key);
        if ($value) {
            return $value;
        }
    
        return Configure::read('CakeTools.settings.status.' . $key);
    }
    
}