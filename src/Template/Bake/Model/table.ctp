<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;

$bakeData = bake_get_model_fields($name, 'model');
extract($bakeData);
%>
<?php
namespace <%= $namespace %>\Model\Table;

<%
$uses = [
    "use $namespace\\Model\\Entity\\$entity;",
    'use Cake\ORM\Query;',
    'use Cake\ORM\RulesChecker;',
    'use Cake\ORM\Table;',
    'use Cake\Validation\Validator;'
];

$usesCustom = [
    'use CakeTools\Model\Validation\GlobalValidator;'
];

sort($uses);
echo implode("\n", $uses);
sort($usesCustom);
echo "\n";
echo implode("\n", $usesCustom);
%>


/**
 * <%= $name %> Model
<% if ($associations): %>
 *
<% foreach ($associations as $type => $assocs): %>
<% foreach ($assocs as $assoc): %>
 * @property \App\Model\Table\<%= $assoc['alias'] %>Table $<%= $assoc['alias'] %>
<% endforeach %>
<% endforeach; %>
<% endif; %>
 */
class <%= $name %>Table extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

<% if (!empty($table)): %>
        $this->table('<%= $table %>');
<% endif %>
<% if (!empty($_displayField) || !empty($displayField)): %>
        $this->displayField('<% echo !empty($_displayField) ? $_displayField : $displayField %>');
<% endif %>
<% if (!empty($primaryKey)): %>
<% if (count($primaryKey) > 1): %>
        $this->primaryKey([<%= $this->Bake->stringifyList((array)$primaryKey, ['indent' => false]) %>]);
<% else: %>
        $this->primaryKey('<%= current((array)$primaryKey) %>');
<% endif %>
<% endif %>
<% if (!empty($behaviors)): %>

<% endif; %>
<% foreach ($behaviors as $behavior => $behaviorData): %>
        $this->addBehavior('<%= $behavior %>'<%= $behaviorData ? ", [" . implode(', ', $behaviorData) . ']' : '' %>);
<% endforeach %>
<% if (!empty($associations)): %>

<% endif; %>
<% foreach ($associations as $type => $assocs): %>
<% foreach ($assocs as $assoc):
	$alias = $assoc['alias'];
	unset($assoc['alias']);
%>
        $this-><%= $type %>('<%= $alias %>', [<%= $this->Bake->stringifyList($assoc, ['indent' => 3]) %>]);
<% endforeach %>
<% endforeach %>
    }
<% if (!empty($validation)): %>

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $gv = new GlobalValidator($validator);
<%

$validationMethods = [];
foreach ($_fields as $field => $info):
    if ($field == 'slug') {
        $validationMethods[] = sprintf(
            "\$gv->slug();"
        );
    } elseif ($field == 'email') {
        $validationMethods[] = sprintf(
            "\$gv->isEmail();"
        );
    } elseif ($info['type'] == 'string') {
        $validationMethods[] = sprintf(
            "\$gv->string('%s', %s);",
            $field,
            $info['length']
        );
    } elseif ($info['type'] == 'text') {
        $validationMethods[] = sprintf(
            "\$gv->text('%s');",
            $field
        );
    } elseif ($field == 'status') {
        // notEmpty
        $validationMethods[] = sprintf(
            "\$gv->status(%s);",
            class_exists($_modelConstStatus) ? "'".$_modelConstStatus."'" : 'true'
        );
    } elseif ($info['type'] == 'integer') {
        if (preg_match('/(_id)$/i', $field)) {
            $validationMethods[] = sprintf(
                "\$gv->foreignKey('%s');",
                $field
            );
        } else {
            $validationMethods[] = sprintf(
                "\$gv->isNumeric('%s', true);",
                $field
            );
        }
    } elseif ($info['type'] == 'float') {
        $validationMethods[] = sprintf(
            "\$gv->isNumeric('%s');",
            $field
        );
    }
    
endforeach;
    if (!empty($validationMethods)):%>
        <%- foreach ($validationMethods as $validationMethod): %>
        <%= $validationMethod %>
        <%- endforeach; %>
        <% endif; %>

        return $validator;
    }
<% endif %>
<% if (!empty($rulesChecker)): %>

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
    <%- foreach ($rulesChecker as $field => $rule): %>
        $rules->add($rules-><%= $rule['name'] %>(['<%= $field %>']<%= !empty($rule['extra']) ? ", '$rule[extra]'" : '' %>));
    <%- endforeach; %>
        return $rules;
    }
<% endif; %>
<% if ($connection !== 'default'): %>

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        return '<%= $connection %>';
    }
<% endif; %>
}
