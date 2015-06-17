<script>
var google_tag_params = {
	ecomm_prodid: <?php echo id2name('ga_ecomm_prodid',$head,"''") ?>,
	ecomm_pagetype: <?php echo id2name('ga_ecomm_pagetype',$head,"''") ?>,
	ecomm_pname: <?php echo id2name('ga_ecomm_pname',$head,"''") ?>,
	ecomm_pcat: <?php echo id2name('ga_ecomm_pcat',$head,"''") ?>,
	ecomm_pvalue: <?php echo id2name('ga_ecomm_pvalue',$head,"''") ?>
};
var dataLayer = window['dataLayer'] || [];
dataLayer = [{
	google_tag_params: window.google_tag_params
	<?php if($user !== false){ ?>
	, 'UserID': '<?php echo $user['user_id'] ?>'
	<?php } ?>
	<?php if(isset($datalayer_new_customer)){ ?>
	, 'new_customer': '<?php echo $datalayer_new_customer?>'
	<?php } ?>
	<?php if(isset($datalayer_order_sn)){ ?>
	, 'orderId': '<?php echo $datalayer_order_sn?>'
	<?php } ?>
	<?php if(isset($pageCategory)){ ?>
	, 'pageCategory': '<?php echo $pageCategory?>'
	<?php } ?>
	<?php if(isset($datalayer_goods_info)){ ?>
		<?php foreach ($datalayer_goods_info as $key => $value) { ?>
			<?php echo ', "' . $key . '":' . $value; ?>
		<?php } ?>
	<?php } ?>
	<?php if(isset($addon_datalayer_info)){ ?>
		<?php foreach ($addon_datalayer_info as $key => $value) { ?>
			<?php echo ', "' . $key . '":' . $value; ?>
		<?php } ?>
	<?php } ?>
}];
</script>

<?php if(isset($product_ga) && $product_ga['pay_code'] != 'bank' && $product_ga['ordervalue']){ ?>
	<script>dataLayer = [{'ordervalue': '<?php echo $product_ga['ordervalue'] ?>'}];</script>
<?php } ?>
<!--
<script>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-32553607-1']);
_gaq.push(['_setDomainName', '<?php echo COMMON_DOMAIN;?>']);
_gaq.push(['_trackPageview']);
(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
-->
<!-- Google Tag Manager -->

<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-PTWPVK"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PTWPVK');</script>