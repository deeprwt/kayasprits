<?php
$this->buildMlHead();
$this->buildMlBodyBegin();
?>
<div class="a_n a_listing fileslist_inner">
    <span class="nav_active gone remOnMore"><?php echo $folder->getCPage(); ?></span>
    <?php
    //$nav=Navigation::pageCA($folder->getTotalFilesCount(),'',$folder->getFilesLimit(),$folder->getCPage());
    //echo $nav;
    $i = 0;
    $recentlyAdded = $folder->getRecentlyAdded();
    foreach ($files as $file) {
	$i++;
	$mbox = '';
	$rel = '';
	if (preg_match('/(jpg|jpeg|png|bmp|gif)$/i', $file->getType())) {
	    $mbox = 'mbox ';
	    $rel = 'noDesc,width:800';
	}
	?>
        <div class="mediafile-thumb <?php echo $i % 2 == 0 ? 'even' : 'odd'; ?>-row <?php echo $file->getFullName() == $recentlyAdded ? 'selectedData' : ''; ?>">
    	<div class="mediacheck padded gone">
    <?php if ($file->getName() != '..') { ?>
		    <input class="mediacheckbox" type="checkbox" name="media[]" value="<?php echo $file->getFullName(); ?>" />
		<?php } ?>
    	</div>
    	<div class="mediadata-thumb">
    	    <div class="mediaimg-thumb">
    <?php
    if (preg_match('/(jpg|jpeg|png|bmp|gif)$/i', $file->getType())) {
	?>
			<a class="mbox entryhref <? echo ($file->getFullName()==$recentlyAdded?"recent":""); ?>" target="_self" href="<?php echo $file->getFullPath(); ?>" rel="<?php echo $rel; ?>">
			   <img class="img-thumb-big" src="<?php echo $file->getFullPath(true); ?>" />
			</a>
	<?php
    } elseif (preg_match('/(mp3|wav)$/i', $file->getType())) {
	?>
			<i class="fa fa-music fa-5x fa-fw"></i>
			<br />
			<?php
			echo $file->getFullName();
		    } elseif (preg_match('/(avi|cam|flv|mov|mpeg|mpg|swf|wmv|mp4|ogg|webm)$/i', $file->getType())) {
			?>
			<i class="fa fa-video-camera fa-5x fa-fw"></i>
			<br />
			<?php
			echo $file->getFullName();
		    } elseif ($file->getType() == 'dir') {
			if ($file->getName() == '..') {
			    ?>
	    		<span class="fa-stack fa-fw libview_stack-thumb">
	    		    <i class="fa fa-folder-o fa-stack-2x fa-fw libview_folder-thumb"></i>
	    		    <i class="fa fa-level-up fa-stack-1x fa-fw libview_level_up-thumb"></i>
	    		</span>
			    <?php
			} else {
			    ?>
	    		<i class="fa fa-folder-o fa-5x fa-fw"></i>
			    <?php
			}
		    } else {
			?>
			<i class="fa fa-file-text-o fa-5x fa-fw"></i>
			<br />
			<?php
			echo $file->getFullName();
		    }
		    ?>
    	    </div>
    	    <div class="mediatext<?php echo $file->getType() == 'dir' ? '' : '-thumb'; ?>" >
		    <?php
		    echo $file->getName(TRUE) . ($file->getType() == 'dir' ? '' : '.' . $file->getType());
		    if ($file->getName() != '..') {
			?>
			<div class="mediastatus-thumb"><?php echo $file->getSize(true, true); ?></div>
			<?php if ($file->getType() != 'dir') { ?>
	    		<div class="mediastatus-thumb"><?php echo $file->getModedDate(); ?></div>
			<?php } ?>
			<div class="entrynav" >
			    [<span class="entrynavElem delBtn" ><?php echo LBL_DELETE; ?></span>]
			</div>
			<?php
		    }
		    ?>

    	    </div>
    	    <div class="clear-both"></div>
    	</div>

    	<div class="clear-both"></div>
        </div>
		    <?php
		}
		?>
    <div class="clear-both remOnMore"></div>
		<?php
		if (ceil($folder->getTotalFilesCount() / $folder->getFilesLimit()) > $folder->getCPage()) {
		    ?>

        <div class="remOnMore" style="text-align: center;"><button id="showMoreButton"><?php echo LBL_SHOW_MORE; ?></button></div>
    <?php
}
?>
</div>
<div class="listfooter" >
    <div class="delete_btn hidden"><?php echo LBL_DELETE; ?></div>
    <?php echo $folder->displayNav(); ?>
    <div class="clear-both"></div>
</div>
</div>
</body>

</html>
