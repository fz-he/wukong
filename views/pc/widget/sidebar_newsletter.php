<div class="pt10">
<div class="edm_door door_<?php echo $language_code ?>">
	<form action="<?php echo eb_gen_url('newsletter_public/subscribe') ?>" method="post" id="subscribeSubmit">
		<a href="<?php echo eb_gen_url('newsletter_public') ?>" class="edm_door_href"></a>
		<p class="enter_input"><input type="text" id="enterInputText" name="email" data-value="<?php echo lang('subscribe_email') ?>"></p>
		<p><button type="submit" class="edm_btn">&nbsp;</button></p>
	</form>
</div>
</div>
<script>
$(function(){
	ec.form.tips.label("#enterInputText");
	$('#subscribeSubmit').submit(function(){
		var val = $('#enterInputText').val().trim();
		var url = '<?php echo eb_gen_url('newsletter_public') ?>';
		var emailReg = ec.form.regex.email;
		if(!val || !val.match(emailReg)){
			window.location.href= url;
			return false;
		}
	});
});
</script>