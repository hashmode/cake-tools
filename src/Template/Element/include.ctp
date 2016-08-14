<script type="text/javascript">
var CT_WEB_ROOT = '<?php echo $this->request->webroot;?>';
var CT_ROUTER_NAME = '<?php echo CT_ROUTER_NAME;?>';
var CT_CSRF_TOKEN = '<?php echo defined('_CSRF_TOKEN') ? _CSRF_TOKEN : '';?>';
var CT_TEXT = '<?php echo json_encode($this->Assistant->getConfig('text.view'));?>';
</script>

<?php
    if (!isset($css) || $css !== false) {
        echo $this->Html->css('CakeTools.core.css');
    }
    if (!isset($js) || $js !== false) {
        echo $this->Html->script('CakeTools.core.js');
    }
?>
