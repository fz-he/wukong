<?php header("Content-Type:text/html;charset=utf-8");?>
<!doctype html>
<!--[if lt IE 7]> <html class="ie6 oldIE"> <![endif]-->
<!--[if IE 7]>    <html class="ie7 oldIE"> <![endif]-->
<!--[if IE 8]>    <html class="ie8 oldIE"> <![endif]-->
<!--[if gt IE 8]><!-->
<html lang="<?php echo ($language_code == 'us') ? 'en' : $language_code; ?>">
<!--<![endif]-->
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php echo id2name('canonical',$head) ?>
<?php if(isset($head['html_google_rel'])){?>
    <?php foreach($head['html_google_rel'] as $lang => $record){?>
    <link rel="alternate" hreflang="<?php echo $lang ?>" href="<?php echo $record ?><?php
    	if( $this->router->class == 'category' ){
			$rsegmentsTmp = $this->uri->rsegments;
			if( !( isset( $rsegmentsTmp[4] ) && (int)$rsegmentsTmp[4] > 1 ) ){ echo '/' ;}
		}?>" />
    <?php } ?>
<?php } ?>
<?php if(isset($head['html_google_rel_seo'])){ ?>
	<?php foreach($head['html_google_rel_seo'] as $lang => $record){ ?>
    <link rel="alternate" href="<?php echo $record ?><?php if($this->router->class == 'category'){
    	$rsegmentsTmp = $this->uri->rsegments;
    	if( !( isset( $rsegmentsTmp[4] ) && (int)$rsegmentsTmp[4] > 1 ) ){ echo '/' ;}
    }?>" />
	<?php } ?>
<?php } ?>

<meta name="msvalidate.01" content="C4D1A74EB0279310FD569745CB65846A" />
<link rel="shortcut icon" href="/favicon.ico?v=<?php echo STATIC_FILE_VERSION ?>" />
<link rel="icon" href="/animated_favicon.gif?v=<?php echo STATIC_FILE_VERSION ?>" type="image/gif" />
<?php
/*	TODO:
 *	CSSPath and javascriptPath
 */
$cssPath = 'css_v2';
$jsPath = 'js_v2';
$mediaPath = '/images';
$cdnPath = '';