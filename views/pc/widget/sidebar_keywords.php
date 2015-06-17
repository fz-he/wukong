<?php if(isset($keyword_list) && count($keyword_list) > 0){ ?>
	<div class="side_list mod mt10">
        <div class="title"><h4><?php echo lang('related_keywords'); ?></h4></div>
        <div class="mod_content p10">
        <ul>
            <?php foreach($keyword_list as $record){ ?>
            <li><a href="/buy-<?php echo $record['word_url'] ?>.html"><?php echo $record['word'] ?></a></li>
            <?php } ?>
        </ul>
        </div>
    </div>
<?php }