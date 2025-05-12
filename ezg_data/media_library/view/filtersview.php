<div class="filterssection w99">
	<div class="typeFilters rvts8">
		<button class="typeFilter underlined" id="type_all" rel="All"><i class="fa fa-star"></i></button>
		<button class="typeFilter" id="type_images" rel="Images"><i class="fa fa-picture-o"></i></button>
		<button class="typeFilter" id="type_video" rel="Videos"><i class="fa fa-video-camera"></i></button>
		<button class="typeFilter" id="type_audio" rel="Audio"><i class="fa fa-music"></i></button>
	</div>
	<div class="vmodes">
		<button class="vmode  <?php echo ($this->viewMode=='norm'?'selectedFilter':'');?>" rel="norm"><i class="fa fa-list"></i></button>
		<button class="vmode  <?php echo ($this->viewMode=='thumb'?'selectedFilter':'');?>" rel="thumb"><i class="fa fa-table"></i></button>
		<div class="clear-both"></div>
	</div>
	<div class="locationArea a_tabletitle">
		<script type="text/javascript">
			if($('#loc').val()!=0) document.write($('#loc').val());
			if($('#protected').val() == 'TRUE') document.write(' <i class="fa fa-lock"></i>');
		</script>
	</div>
	<div class="searcharea" >
		<input type="text" id="libsearch" name="search" value="" />
		<i class="fa fa-search"></i>
	</div>
	<div class="clear-both"></div>
</div>
<?php if($errors!=''):  ?>
	<script> alert("<?php echo $errors ?>");</script>
<?php endif; ?>

