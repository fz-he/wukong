
<?php
	$adList = array();
	if(isset($image_ad[4])) $adList[] = $image_ad[4] ;
	if(isset($image_ad[5])) $adList[] = $image_ad[5] ;
	if(isset($image_ad[9])) $adList[] = $image_ad[9] ;
?>

<?php
foreach($adList as $key => $records){
	foreach ( $records as $record ){
		if(!empty($record) && isset($record['image_link']) && isset($record['image_path'])) {
		//	echo "<a onclick=\"dataLayer.push({'event': 'promotionClick','ecommerce': {'promoClick': {'promotions': [{'id': '{$record['image_ad_name']}{$key}','name': '{$record['image_alt']}'}]}}})\" href=\"{$record['image_link']}\" title=\"{$record['image_alt']}\"><img src=\"/images/common/other/default.png\" data-lazysrc=\"{$record['image_path']}\" title=\"{$record['image_alt']}\" /></a>";
	?>
			<a onclick=" dataLayer.push({'event': 'promotionClick','ecommerce':{'promoClick':{ 'promotions':[{'id':'<?php echo addslashes(  $record['image_ad_name'] ). $key;?>', 'name':'<?php echo addslashes( $record['image_alt'] );?>' }] } }})" href='<?php  echo $record['image_link'] ?>' ><img src="/images/common/other/default.png" data-lazysrc="<?php echo $record['image_path']?>" title="<?php echo $record['image_alt'] ?>" /></a>
	<?php }
	}
}
?>