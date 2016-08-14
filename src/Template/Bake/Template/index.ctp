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

$tableClass = Configure::read('CakeTools.bake_config.tableClass');
$buttonType = Configure::read('CakeTools.bake_config.buttonType');
$actionsElement = Configure::read('CakeTools.bake_config.actionsElement');
$paginationElement = Configure::read('CakeTools.bake_config.paginationElement');

$bakeData = bake_get_model_fields($modelClass, 'index');
extract($bakeData);

%>
<div class="<%= $pluralVar %> index content">
    <h3><?php echo __('<%= $pluralHumanName %>') ?></h3>
<%
	if (!empty($_actions['add'])) {
		$_actions['add'] = is_string($_actions['add']) ? $_actions['add'] : '/'.$modelClass.'/add';
		
		if (!empty($prefix)) {
			$_actions['add'] = strtolower($prefix) . '/' . $_actions['add'];
		}
		 
		$addOptionStr = "['icon' => 'plus'";

		if ($buttonType) {
			$addOptionStr .= ", 'btn' => ".$buttonType."]";
		} else {
			$addOptionStr .= ", 'btn' => true]";
		}
%>
    <div class="row mb10">
    	<div class="col-sm-12">
			<?php echo $this->Assistant->link(__('Add'), '<%=$_actions['add'];%>', <%=$addOptionStr;%>); ?>
		</div>
	</div>
<%
	}
%>   
	<?php if (count($<%=$pluralVar;%>) > 0) {?> 
    <table cellpadding="0" cellspacing="0" class="<%=$tableClass%> <% echo $_listCheckbox ? 'checkbox-group' : ''; %>">
        <thead>
            <tr>
<% foreach ($_fields as $field => $fieldData): %>
<%
	if ($field == 'id') {
		if ($_listCheckbox) {

%>
                <th class="ta-center"><input type="checkbox" class="checkbox-parent" value="check_all" /></th>
<%

		}
	} elseif ($field == 'status' && class_exists($_modelConstStatus)) {

%>
                <th><?php echo $this->Paginator->sort('<%=$field;%>'); ?></th>
<%
		
	} elseif ($field == 'status') {

%>
                <th><?php echo $this->Paginator->sort('<%=$field;%>', __('Active?')); ?></th>
<%

	} else {

%>
		        <th><?php echo $this->Paginator->sort('<%=$field;%>'); ?></th>
<%

	}

endforeach; %>
                <th class="actions"><?php echo __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($<%= $pluralVar %> as $<%= $singularVar %>): ?>
            <tr>
<%        
	
        $hasStatus = false; 
        foreach ($_fields as $field => $fieldData) {

			if ($field == 'status') {
				$hasStatus = true;
			}
			
            $isKey = false;
            if (!empty($associations['BelongsTo'])) {
                foreach ($associations['BelongsTo'] as $alias => $details) {
                    if ($field === $details['foreignKey']) {
                        $isKey = true;
%>
                <td><?php echo $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['url' => '/<%= $details['controller'] %>/view/'.$<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]) : '' ?></td>
<%
                        break;
                    }
                }
            }
            
            if ($isKey !== true) {
            
				if ($field == 'id') {
					if ($_listCheckbox) {
%>
				<td class="ta-center"><input type="checkbox" class="checkbox-child" value="check_<?php echo $<%=$singularVar;%>-><%=$primaryKey[0]%>;?>" /></td>
<%

					}
				} elseif ($field == 'status' && class_exists($_modelConstStatus)) {
				
%>
				<td><?php echo getClassConstant('<%=$_modelConstStatus%>', $<%=$singularVar;%>-><%=$field;%>); ?></td>
<%

				} elseif ($field == 'status') {

				
%>
				<td><?php echo yesNo($<%=$singularVar;%>-><%=$field;%>); ?></td>
<%
				    
				} elseif ($fieldData['type'] == 'boolean') {
					
%>
			    <td><?php echo yesNo($<%=$singularVar;%>-><%=$field;%>); ?></td>
<%
					
				} elseif ($fieldData['type'] == 'date') {
					
%>
				<td><?php echo getUserDate($<%=$singularVar;%>-><%=$field;%>); ?></td>
<%
					
				} elseif ($schema->columnType($field) == 'datetime') {
					
%>
				<td><?php echo getUserDateTime($<%=$singularVar;%>-><%=$field;%>); ?></td>
<%
					
				} else {
					
%>
				<td><?php echo shorten($<%=$singularVar;%>-><%=$field;%>); ?></td>
<%
					
				}
            
            }
        }

        $pk = '$' . $singularVar . '->' . $primaryKey[0];
%>
                <td class="actions">
<%
			$orderAction = false;
			$viewAction = false;
			$editAction = false;
			$deleteAction = false;
			if (!empty($_actions)) {
				if (!empty($_actions['order'])) {
    				$orderAction = is_string($_actions['order']) ? $_actions['order'] . $singularVar->$primaryKey[0] : $_actions['order'];
				}
				
				if (!empty($_actions['view'])) {
    				$viewAction = is_string($_actions['view']) ? $_actions['view'] . $singularVar->$primaryKey[0] : $_actions['view'];
				}
				
				if (!empty($_actions['edit'])) {
    				$editAction = is_string($_actions['edit']) ? $_actions['edit'] . $singularVar->$primaryKey[0] : $_actions['edit'];
				}
				
				if (!empty($_actions['delete'])) {
    				$deleteAction = is_string($_actions['delete']) ? $_actions['delete'] . $singularVar->$primaryKey[0] : $_actions['delete'];
				}
			}
			$statusStr = $hasStatus ? "'statusValue' => $".$singularVar."->status" : "''";
%>
        			<?php 
        			     echo $this->element('<%=$actionsElement;%>', 
        			         [
        			             'idValue' => $<%=$singularVar;%>-><%=$primaryKey[0]%>,
        						 'modelValue' => '<%=$modelClass;%>', 
        						 <%=$statusStr%>,
        						 'orderAction' => <%=$orderAction%>,
        						 'viewAction' => <%=$viewAction%>,
        						 'editAction' => <%=$editAction%>,
        						 'deleteAction' => <%=$deleteAction%>
        			         ]
        			     );
        			?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php echo $this->element('<%=$paginationElement;%>');?>
    
	<?php } else { ?>
    	<div class="row empty-list">
    		<div class="col-md-12">
    			<?php echo __('No records');?>
    		</div>
		</div>	
    <?php }?>
</div>
