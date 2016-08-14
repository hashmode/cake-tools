<?php 
/**
 *  Usage 
 *  
 *  add attributes to the cliking element
 *  class - alertBox
 *  message - message to be displayed
 *  
 */
?>

<div class="modal" id="alertBox" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="alertBoxHeader"><?php echo __('Information')?></h4>
			</div>
			<div class="modal-body">
				<div id="alertBoxMessage" class="row-fluid">
				    
        		</div>
			</div>
			<div class="modal-footer">
                <button class="btn btn-success" data-dismiss="modal" aria-hidden="true"><?php echo __('Close')?></button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->


<script type="text/javascript">
function alertBox(message, message2) {
	$("#alertBoxMessage").html(message);
	if (typeof message2 !== 'undefined') {
		$("#alertBoxMessage").html(message2);	
		$("#alertBoxHeader").html(message);	
	}
	
	$("#alertBox").modal('show');
}
</script>


<?php 
/**
 *  Usage 
 *  
 *  add attributes to the cliking element
 *  class - confirmBox
 *  message - message to be displayed
 *  href - to go if the action is confirmed
 *  
 */
?>

<div class="modal" id="confirmBox" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="confirmBoxHeader"><?php echo __('Please Confirm Your Action')?></h4>
			</div>
			<div class="modal-body">
				<div id="confirmBoxMessage" class="row-fluid">
				    
        		</div>
			</div>
			<div class="modal-footer">
			   <button class="btn btn-success" data-dismiss="modal" aria-hidden="true" id="confirmModalCancel"><?php echo __('Cancel')?></button>
    	       <button class="btn btn-danger" id="confirmBoxConfirm"><?php echo __('Confirm')?></button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script type="text/javascript">
$CT_CONFIRM_NODE = false;
var CT_CONFIRM_BOX_STATUS = false;
var CT_CONFIRM_BOX_CALLBACK = false;

function confirmBox(message, callback) {
	CT_CONFIRM_BOX_STATUS = false;
	$("#confirmBoxMessage").html(message);
	$("#confirmBox").modal('show');

	if (typeof callback !== 'undefined') {
		CT_CONFIRM_BOX_CALLBACK = callback;
	} else {
		CT_CONFIRM_BOX_CALLBACK = false;
	}

	return true;
}

$(document).ready(function(){
	$(document).on('click', "#confirmBoxConfirm", function() {
		$("#confirmBox").modal('hide');

		if ($CT_CONFIRM_NODE) {
    		$CT_CONFIRM_NODE.removeAttr("message");
    		$CT_CONFIRM_NODE.trigger('click');
		}
	});

	$(document).on('click', ".confirmBox", function(e) {
		$CT_CONFIRM_NODE = $(this);
		var message = $CT_CONFIRM_NODE.attr('message');
		if(message) {
			$("#confirmBoxMessage").html(message);
			$("#confirmBox").modal('show');
			return false;
		}

		if (!$(this).attr('noredirect')) {
			window.location = $(this).attr('href');
		}
	});
});
</script>