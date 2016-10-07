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

$bakeConfig = Configure::read('CakeTools.bake_config');
$modelConstStatus = "Const".$modelClass."Status";

$associations += ['BelongsTo' => [], 'HasOne' => [], 'HasMany' => [], 'BelongsToMany' => []];
$immediateAssociations = $associations['BelongsTo'] + $associations['HasOne'];
$associationFields = collection($fields)
    ->map(function($field) use ($immediateAssociations) {
        foreach ($immediateAssociations as $alias => $details) {
            if ($field === $details['foreignKey']) {
                return [$field => $details];
            }
        }
    })
    ->filter()
    ->reduce(function($fields, $value) {
        return $fields + $value;
    }, []);

$groupedFields = collection($fields)
    ->filter(function($field) use ($schema) {
        return $schema->columnType($field) !== 'binary';
    })
    ->groupBy(function($field) use ($schema, $associationFields) {
        $type = $schema->columnType($field);
        if (isset($associationFields[$field])) {
            return 'string';
        }
        if (in_array($type, ['integer', 'float', 'decimal', 'biginteger'])) {
            return 'number';
        }
        if (in_array($type, ['date', 'time', 'datetime', 'timestamp'])) {
            return 'date';
        }
        return in_array($type, ['text', 'boolean']) ? $type : 'string';
    })
    ->toArray();

$groupedFields += ['number' => [], 'string' => [], 'boolean' => [], 'date' => [], 'text' => []];
$pk = "\$$singularVar->{$primaryKey[0]}";
%>
<div class="<%= $pluralVar %> view content">
    <<%=$bakeConfig['view_heading_tag']%>><?= h($<%= $singularVar %>-><%= $displayField %>) ?></<%=$bakeConfig['view_heading_tag']%>>
    <table class="table table-striped table-bordered vertical-table">
<% if ($groupedFields['string']) : %>
<% foreach ($groupedFields['string'] as $field) : %>
<% if (isset($associationFields[$field])) :
            $details = $associationFields[$field];
%>
        <tr>
            <th><?= __('<%= Inflector::humanize($details['property']) %>') ?></th>
            <td><?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['controller' => '<%= $details['controller'] %>', 'action' => 'view', $<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]) : '' ?></td>
        </tr>
<% else : %>
        <tr>
            <th><?= __('<%= Inflector::humanize($field) %>') ?></th>
            <td><?= h($<%= $singularVar %>-><%= $field %>) ?></td>
        </tr>
<% endif; %>
<% endforeach; %>
<% endif; %>
<% if ($groupedFields['number']) : %>
<% foreach ($groupedFields['number'] as $field) : %>
        <tr>
<%
		if ($field == 'status' && class_exists($modelConstStatus)) {
%>
			<th><?php echo __('Status'); ?></th>
			<td><?php echo getClassConstant($modelConstStatus, $<%=$singularVar%>-><%=$field;%>); ?></td>
<%
		} elseif ($field == 'status') {
%>
			<th><?php echo __('Active?'); ?></th>
			<td><?php echo yesNo($<%=$singularVar%>-><%=$field;%>); ?></td>
<%
        } else {
%>
            <th><?= __('<%= Inflector::humanize($field) %>') ?></th>
            <td><?= $this->Number->format($<%= $singularVar %>-><%= $field %>) ?></td>
<%
        }
%>
        </tr>
<% endforeach; %>
<% endif; %>
<% if ($groupedFields['date']) : %>
<% foreach ($groupedFields['date'] as $field) : %>
        <tr>
<%
            if ($schema->columnType($field) == 'date') {
%>            
            <th><% echo "<% echo __('" . Inflector::humanize($field) . "') %>" %></th>
            <td><?php echo getUserDate($<%= $singularVar %>-><%= $field %>) ?></td>
<%
            } elseif ($schema->columnType($field) == 'datetime') {
%>            
            <th><% echo "<% echo __('" . Inflector::humanize($field) . "') %>" %></th>
            <td><?php echo getUserDate($<%= $singularVar %>-><%= $field %>) ?></td>
<%
            } else {
%>            
            <th><% echo "<% echo __('" . Inflector::humanize($field) . "') %>" %></th>
            <td><?php echo h($<%= $singularVar %>-><%= $field %>) ?></td>
<%
            }
%>
        </tr>
<% endforeach; %>
<% endif; %>
<% if ($groupedFields['boolean']) : %>
<% foreach ($groupedFields['boolean'] as $field) : %>
        <tr>
            <th><?= __('<%= Inflector::humanize($field) %>') ?></th>
            <td><?= $<%= $singularVar %>-><%= $field %> ? __('Yes') : __('No'); ?></td>
         </tr>
<% endforeach; %>
<% endif; %>
    </table>
<% if ($groupedFields['text']) : %>
<% foreach ($groupedFields['text'] as $field) : %>
    <div class="row">
    	<div class="col-md-12">
            <h4><?= __('<%= Inflector::humanize($field) %>') ?></h4>
            <?= $this->Text->autoParagraph(h($<%= $singularVar %>-><%= $field %>)); ?>
    	</div>
    </div>
<% endforeach; %>
<% endif; %>
</div>
