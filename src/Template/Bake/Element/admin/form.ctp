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

$formClass = Configure::read('CakeTools.bake_config.formClass');
$buttonClass = Configure::read('CakeTools.bake_config.buttonClass');
$buttonType = Configure::read('CakeTools.bake_config.buttonType');
$actionsElement = Configure::read('CakeTools.bake_config.actionsElement');
$tinymceMin = Configure::read('CakeTools.bake_config.tinymceMin');
$tinymceInit = Configure::read('CakeTools.bake_config.tinymceInit');

$bakeData = bake_get_model_fields($modelClass, 'form');
extract($bakeData);

if (count($_tinymceFields) > 0) {
%>
<?php echo $this->Html->script('<% echo $tinymceMin; %>'); ?>
<%
}
%>

<div class="<%= $pluralVar %> form content <% echo $formClass %>">
    <?php echo $this->Form->create($<%= $singularVar %>) ?>
        <h2><?php echo __('<%= Inflector::humanize($action) %> <%= $singularHumanName %>') ?></h2>
        <?php
<%

        foreach ($_fields as $field => $fieldData) {
        	$optionsStr = "";
        	$isSelect = false;
            if (in_array($field, $_tinymceFields)) {
            	$optionsStr =  "['class' => 'tinymce', 'type' => 'textarea']";
            } elseif ($fieldData['type'] == 'text') {
            	$optionsStr =  "['type' => 'textarea']";
            } elseif ($field == 'status' && $_modelConstStatus && class_exists($_modelConstStatus)) {
            	$optionsStr =  "['options' => '".$_modelConstStatus."']";
            	$isSelect = true;
            } elseif ($field == 'status') {
            	$optionsStr =  "['type' => 'checkbox', 'label' => __('Is Active')]";
            } elseif ($fieldData['type'] == 'boolean') {
            	$optionsStr =  "['type' => 'checkbox']";
            } elseif (strstr($field, '_id')) {
            	$isSelect = true;
            	$optionsStr =  "";
            }

            if (!$optionsStr && !$isSelect) {
            	$optionsStr =  "['type' => 'text']";
            }
            
            if ($isSelect) {
%>
            echo $this->Assistant->select('<%= $field %>'<% echo !empty($optionsStr) ? ', '.$optionsStr : ''; %>);
<%
            } else {
%>
            echo $this->Form->input('<%= $field %>', <%= $optionsStr; %>);
<%
            }
        }
        if (!empty($associations['BelongsToMany'])) {
            foreach ($associations['BelongsToMany'] as $assocName => $assocData) {
%>
            echo $this->Form->input('<%= $assocData['property'] %>._ids', ['options' => $<%= $assocData['variable'] %>, 'multiple' => 'checkbox']);
<%
            }
        }
        
        $submitOptions = "";
        if ($buttonType) {
            $submitOptions = ", ['btn' => '".$buttonType."'";
        }

        if ($buttonClass) {
            if ($submitOptions) {
                $submitOptions .= ", 'class' => '".$buttonClass."' ";
            } else {
                $submitOptions = ", ['class' => '".$buttonClass."'";
            }
        }

        if ($submitOptions) {
            $submitOptions .= "]";
        }
        
%>
        ?>
    	<?php echo $this->Assistant->submit(<% echo $submitOptions ? "''".$submitOptions : ''; %>) ?>
    <?php echo $this->Form->end() ?>
</div>

<%
if (count($_tinymceFields) > 0) {
%>
<?php $this->TinymceElfinder->defineElfinderBrowser()?>
<?php echo $this->Html->script('<% echo $tinymceInit; %>');?>
<%
}
%>
