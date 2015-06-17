<?php if(isset($breadcrumbs)){ ?>
<div class="wrap breadcrumbs">
	<i class="icon_home vam">&nbsp;</i>
	<a href="<?php echo eb_gen_url() ?>" class="vam"><?php echo lang('home') ?></a>
	<?php foreach($breadcrumbs as $record){ ?>
		<i class="icon_arr_right vam">&nbsp;</i>
		<?php if(isset($record['url'])){ ?>
		<a href="<?php echo $record['url'] ?>" class="vam"><?php echo $record['title'] ?></a>
		<?php }else{ ?>
		<span class="vam"><?php echo $record['title'] ?></span>
		<?php } ?>
	<?php } ?>
	<?php if( (!empty( $page_name )) && $page_name === 'search' && $keywordsSearch ){?>
		<i class="icon_arr_right vam">&nbsp;</i>
		<span class="vam"><?php echo $keywordsSearch ;?></span>
	<?php }?>
</div>
<?php }