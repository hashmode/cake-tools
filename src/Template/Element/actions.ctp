<?php 
	$isView = isset($viewAction) && $viewAction ? $viewAction : false;
	$isEdit = isset($editAction) && $editAction ? $editAction : false;
	$statusValue = isset($statusValue) ? $statusValue : false;
	$isDelete = isset($deleteAction) && $deleteAction ? $deleteAction : false;
	$isOrder = isset($orderAction) && $orderAction ? $orderAction : false;
	$isList = isset($listAction) && $listAction ? $listAction : false;

	$modelValue = isset($modelValue) ? lcfirst($this->Assistant->inflector('camelize', $modelValue)) : false;
	$modelValueSingular = isset($modelValue) ? $this->Assistant->inflector('singularize', $modelValue) : '';
	$noun = !empty($nounValue) ? $nounValue : (!empty($modelValueSingular) ? __($modelValueSingular) : __('item'));
	
	$actionConfig = $this->Assistant->getConfig('view.helper.html');
	$textConfig = $this->Assistant->getConfig('text.view');
	if (empty($btnSize)) {
		$btnSize = $actionConfig['action_btn_size'];
	}

	if (empty($btnType)) {
	    $btnType = $actionConfig['action_btn'];
	}
	
	if (empty($btnText)) {
	    $btnText = $textConfig['actions_dropdown'];
	}
	
	$actionList = [
	    'index' => '_index',
	    'view' => '_view',
	    'edit' => '_edit',
	    'status' => '_status',
	    'position' => '_position',
	    'delete' => '_delete',
	];
	
	if (!empty($isPrefix) || (!empty($this->request->params['prefix']) && (!isset($isPrefix) || $isPrefix !== false))) {
	    array_walk($actionList, function(&$val) {
	        $val = '_admin'.$val;
	    });
	    unset($val);
	}

    $reverse = !empty($reverse) ? 'reverse' : '';
?>

<div class="btn-group btn-group-<?php echo $btnSize;?> action-group <?php echo $reverse;?>" role="group">
    <button type="button" class="btn btn-<?php echo $btnType;?> dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <?php echo $btnText;?>
        <span class="caret"></span>
    </button>
	<ul class="dropdown-menu">
	
		<?php $elementExists = false;?>
		<?php if ($isList) {?>
			<li>
				<?php
					$link = is_string($isList) ? $isList : $this->Assistant->{$actionList['index']}($modelValue);
					echo $this->Assistant->link(__('List'), $link, ['icon' => 'eye-open']);
				?>		
			</li>
			<?php $elementExists = true;?>
		<?php }?>
	
		<?php if ($isView) {?>
			<li>
				<?php 
					$link = is_string($isView) ? $isView : $this->Assistant->{$actionList['view']}($idValue, $modelValue);
					echo $this->Assistant->link(__('View'), $link, ['icon' => 'eye-open']);
				?>		
			</li>
			<?php $elementExists = true;?>
		<?php }?>
		
		<?php if ($isEdit) {?>
			<li>
				<?php 
					$link = is_string($isEdit) ? $isEdit : $this->Assistant->{$actionList['edit']}($idValue, $modelValue);
					echo $this->Assistant->link(__('Edit'), $link, ['icon' => 'pencil']);
				?>					
			</li>
			<?php $elementExists = true;?>
		<?php }?>
		
		
		<?php if ($statusValue !== false) {?>
		
			<?php if ($elementExists) {?>
				<li class="divider"></li>
			<?php }?>
		
			<?php 
				$className = 'Const'.$modelValueSingular.'Status';
				
				if (class_exists($className)) {
					$statusList = getClassConstants($className, true);
				} else {
					$statusList = [
						ConstGeneralStatus::Suspended => __('Suspended'),
						ConstGeneralStatus::Active => __('Active')
					];
				} ?>
				
				<?php foreach ($statusList as $stat => $name):?>
					<li>
					<?php
                        $options = [
                            'icon' => 'ok',
                            'class' => 'confirmBox',
                            'message' => __('You are about to change the status to {0}, Continue?', $name)
                        ];
                        if ($stat == $statusValue) {
                            continue;
                        }
                        echo $this->Assistant->link(__('Make {0}', $name), $this->Assistant->{$actionList['status']}($idValue, $stat, $modelValue), $options);
                    ?>		
					</li>
				<?php endforeach;?>
			
				<?php $elementExists = true;?>
		<?php }?>
		
		<?php if ($isOrder) {?>
		
			<?php if ($elementExists) {?>
				<li class="divider"></li>
			<?php }?>
			
			<?php if (is_array($isOrder)) {?>
				<li>
					<?php echo $this->Assistant->link(__('Move Up'), $isOrder['up'], ['icon' => 'arrow-up']);?>
				</li>
				<li>
					<?php echo $this->Assistant->link(__('Move Down'), $isOrder['down'], ['icon' => 'arrow-down']);?>
				</li>
			<?php } else {?>
			    <li>
					<?php 
					   echo $this->Assistant->link(
					       __('Move Up'), 
					       $this->Assistant->{$actionList['position']}($idValue, $modelValue, 1), 
					       ['icon' => 'arrow-up']
					   );
					?>
				</li>
				<li>
					<?php 
					   echo $this->Assistant->link(
					       __('Move Down'), 
					       $this->Assistant->{$actionList['position']}($idValue, $modelValue, -1),
					       ['icon' => 'arrow-down']
					   );
					?>
				</li>
			<?php }?>

			<?php $elementExists = true;?>
		<?php }?>
		
		<?php if (isset($custom) && !empty($custom)) {?>
			<?php if ($elementExists) {?>
				<li class="divider"></li>
			<?php }?>

			<?php foreach ($custom as $action):?>
				<?php if (isset($action['name'])) {?>
					<?php if ($action['name'] == 'divider') {?>
						<li class="divider"></li>
					<?php } else {?>
						<li>
							<?php
    							$linkOptions = [
    							    'icon' => 'glyphicon glyphicon-'.$action['icon'],
    							    'class' => ''
    							];

    							$thisName = $action['name'];
    							$thisUrl = $action['url'];
    							unset($action['name'], $action['url']);

    							if (!empty($action['class'])) {
    							    $linkOptions['class'] .= ' '.$action['class'];
    							    unset($action['class']);
    							}

    							if (!empty($action['message'])) {
    							    $linkOptions['class'] .= ' confirmBox';
    							    $linkOptions['message'] = $action['message'];
    							    unset($action['message']);
    							}
    							
    							if (!empty($action)) {
    							    foreach ($action as $k => $v) {
    							        $linkOptions[$k] = $v;
    							    }
    							}
    							
    							echo $this->Assistant->link($thisName, $thisUrl, $linkOptions);
							?>
						</li>
					<?php }?>
				<?php }?>
			<?php endforeach;?>
		<?php }?>

		<?php if ($isDelete) {?>

			<?php if ($elementExists) {?>
				<li class="divider"></li>
			<?php }?>
		
			<li>
				<?php 
					$link = is_string($isDelete) ? $isDelete : $this->Assistant->{$actionList['delete']}($idValue, $modelValue);
					echo $this->Assistant->link(
					    __('Delete'), 
						$link,
						[
						    'icon' => 'trash text-danger',
						    'class' => 'text-danger confirmBox', 
							'message' => __('Are you sure you want to delete this {0}?', $noun)
					   ]
					);
				?>					
			</li>
		<?php }?>
		
	</ul>
</div>
