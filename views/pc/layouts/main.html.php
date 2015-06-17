<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\components\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $content string */
$head = isset($this->params['head']) ? $this->params['head'] : [] ;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?php echo ($language_code == 'us') ? 'en' : $language_code; ?>"> 
<head>
	<meta charset="<?= Yii::$app->charset ?>">

	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<?php echo ArrayHelper::id2name('canonical',$head) ?>
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
	?>
	<title><?php echo ArrayHelper::id2name('title',$head) ?></title>
	<meta name="keywords" content="<?php echo htmlspecialchars(id2name('keywords',$head).id2name('keywords_desc_domain',$head)) ?>" />
	<meta name="description" content="<?php echo htmlspecialchars(id2name('description',$head)) ?>" />
	<!-- narrow search mate is use -->
	<link rel="stylesheet" media="all" href="<?php echo HelpUrl::css('common.css' , $cssPath) ?>" />
	<?php include eb_view_path_new('common/common_js.php'); ?>
	<link rel="stylesheet" media="all" href="<?php echo HelpUrl::css('index.css' , $cssPath) ?>" />
	
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <? Html::csrfMetaTags() ?>
    <title><?= Html::encode( ArrayHelper::id2name('title',$head) ) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
	
	<?= $this->render('//pc/common/top.php') ?>
	
        <div class="container">
            <?= $content ?>
        </div>

	<?= $this->render('//pc/common/footer.php') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
