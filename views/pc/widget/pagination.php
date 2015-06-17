
<?php if(isset($pagination) && $pagination['total_page'] > 1){ ?>
<div class="ec_pager">
	<?php
		$pageListNumber = 9; //最大显示的分页数字按钮
		$page = $pagination['current_page']; //当前页码
		$href = $pagination['href'];
		$defaultHref = $pagination['default_href'];
		$total = $pagination['total_page']; //总页数

		$prevClass = ($page <= 1) ? 'p_btn_un' : 'p_prev';
        $prevRel = ($page <= 1) ? '' : 'rel = "prev" ';
	//	$prevHref = ($page <= 1) ? 'javascript:;' : $page == 2 ? $defaultHref : sprintf($href, ($page-1)) ;
        if($page <= 1){
            $prevHref = 'javascript:;';
        }elseif($page == 2){
            $prevHref = $defaultHref;
        }else{
            $prevHref = sprintf($href, ($page-1));
        }
		$nextClass = ($page >= $total) ? 'p_btn_un' : 'p_next';
        $nextRel = ( $page >= $total ) ? '' : 'rel = "next"';
		$nextHref = ($page >= $total) ? 'javascript:;' : sprintf($href, ($page+1));

	?>

	<a class="<?php echo $prevClass?>" href="<?php echo $prevHref?>" <?php echo $prevRel;?> >&lt;&nbsp;<?php echo lang('page_prev') ?></a>
	<?php
		$min = ($total > $pageListNumber && $page >= $pageListNumber) ? ($page - ($pageListNumber-1)/2) : 1;
		$n = ($pageListNumber-1)/2 - ($total-$page);
		if($n > 0){
			$min = ($min - $n > 0) ? ($min - $n) : 1;
		}

		if($min > 2) {
			echo '<a href="'. $defaultHref .'">1</a>&nbsp;...&nbsp;';
		}
		for($i = $min; $i < ($min + $pageListNumber); $i++){
			if( $page == $i ){
				echo '<a class="current" href="javascript:;">'. $i .'</a>' ;
			}else{
                if($page == $i+1)
                    $pageRel = 'rel = "prev" ';
                else if($page == $i-1)
                    $pageRel = 'rel = "next" ';
                else
                    $pageRel = '';
				if( $i === 1){
					echo '<a href="'. $defaultHref . '"' . $pageRel . '>'. $i .'</a>';
				}else{
					echo '<a href="'. sprintf($href, $i). '"' . $pageRel . '>'. $i .'</a>';
				}
			}
			if($i == $total) break;
		}
		if($total >= ($min + $pageListNumber)) {
			echo '&nbsp;...&nbsp;<a href="'. sprintf($href,$total) .'">'. $total .'</a>';
		}
	?>
	<a class="<?php echo $nextClass?>" href="<?php echo $nextHref?>" <?php echo $nextRel;?> ><?php echo lang('page_next') ?>&nbsp;&gt;</a>

</div>
<?php } ?>