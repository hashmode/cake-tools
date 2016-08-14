<?php echo $this->assign('title', __('Login'))?>

<h1 class="page-header"><?php echo __('Login') ?></h1>

<div>
    <?php echo $this->Form->create('Users', ['novalidate'])?>
    	<?php echo $this->Form->input($authFields['username'])?>
    	<?php echo $this->Form->input($authFields['password'], ['value' => '', 'autocomplete' => 'off'])?>
    	
    	<?php if (!empty($showCaptcha)) {?>
    		<div>
    			<img 
    				id="signupCaptchaImg" 
    				class="signup-captcha-change" 
    				src="<?php echo $this->Url->build($this->Assistant->url('/users/captchaImage/'.time()));?>" />
    		</div>

        	<div>
        		<a href="#" class="signup-captcha-change"><?php echo __("Show another image");?></a>
        	</div>
        	
        	<div>
    			<?php
                    echo $this->Form->input('captcha', [
                        'type' => 'text',
                        'value' => '',
                        'div' => false,
                        'placeholder' => __('Please type the characters from the image'),
                        'label' => false,
                        'autocomplete' => 'off'
                    ]);
                ?>
    		</div>
    	<?php }?>
    	
    	<?php echo $this->Assistant->submit()?>
    <?php echo $this->Form->end()?>

</div>
