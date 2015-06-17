<?php include eb_view_path_new('common/base.php'); ?>
<title><?php echo id2name('title',$head) ?></title>
<meta name="keywords" content="<?php echo htmlspecialchars(id2name('keywords',$head).id2name('keywords_desc_domain',$head)) ?>" />
<meta name="description" content="<?php echo htmlspecialchars(id2name('description',$head)) ?>" />
<!-- narrow search mate is use -->
<link rel="stylesheet" media="all" href="<?php echo HelpUrl::css('common.css' , $cssPath) ?>" />
<?php include eb_view_path_new('common/common_js.php'); ?>
