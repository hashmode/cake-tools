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
 
$currentModelData  = bake_get_model_fields($currentModelName, 'index');
$lastField = getLastKey($currentModelData['_fields']);

$belongsTo = bake_get_belongs_to($modelObj);
if (!empty($belongsTo)) {
	$belongsTo = array_intersect_key($belongsTo, $currentModelData['_fields']);
}
%>

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
    	$options = [
    		'fields' => [
<% foreach($currentModelData['_fields'] as $field => $fieldInfo): %>
                '<%=$currentModelName;%>.<%=$field;%>'<% echo $field == $lastField ? '' : ","; %>
<% endforeach;%>
    		]<% echo !empty($belongsTo) ? ",\n" : "\n";%>
<% 
	if (!empty($belongsTo)): 
		$lastKey = getLastKey($belongsTo);
%>
			'contain' => [
<% foreach($belongsTo as $foreignKey => $thisModelObj): %>
                '<%=$thisModelObj->alias();%>' => [
                    'fields' => [
                        '<%=$thisModelObj->alias();%>.id',
                        '<%=$thisModelObj->alias();%>.<%=$thisModelObj->displayField();%>'
                    ]
                ]<% echo $foreignKey == $lastKey ? "\n" : ",\n"; %>
<% endforeach; %>
			]
<%
	endif; 
%>
    	];
        $<%= $pluralName %> = $this->Paginator->paginate($this-><%= $currentModelName %>, $options);

        $this->set(compact('<%= $pluralName %>'));
    }
