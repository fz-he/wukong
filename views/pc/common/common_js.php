<!--[if lt IE 9]>
<script src="<?php echo HelpUrl::js('jquery-1.11.1.js' ,  $jsPath.'/libs') ?>"></script>
<script src="<?php echo HelpUrl::js('html5shiv.js' ,  $jsPath.'/common') ?>"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script src="<?php echo HelpUrl::js('jquery-2.1.1.js' ,  $jsPath.'/libs') ?>"></script>
<!--<![endif]-->
<script src="<?php echo HelpUrl::js('ec.lib.js' , $jsPath.'/libs') ?>" namespace="ec"></script>


<!--[if IE 6]><script>ol.isIE6=true;</script><![endif]-->
<!--[if IE 7]><script>ol.isIE7=true;</script><![endif]-->
<!--[if IE 8]><script>ol.isIE8=true;</script><![endif]-->
<script>
<?php echo (FRONT_DEBUG) ? 'ol.debug = true;' : ''?>
ol.mediaPath = '<?php echo $mediaPath?>';
ol.version = '<?php echo STATIC_FILE_VERSION ?>';
ol.lang = {globle : "<?php echo $language_code ?>"};
<?php
#登录信息
if ($user != false) {
	echo 'ol.loginInfo = {';
	echo '"user_name":"'. id2name('user_name',$user).'"';
	echo '};';
}
?>
</script>
<?php include eb_view_path_new('common/page_header_ga.php'); ?>