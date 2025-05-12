<div class="filterssection" >
	<div class="typeFilters">
		<span class="typeFilter selectedFilter" id="type_all" rel="All"><i class="fa fa-star"></i></span>
		|
		<span class="typeFilter" id="type_images" rel="Images"><i class="fa fa-picture-o"></i></span>
		|
		<span class="typeFilter" id="type_video" rel="Videos"><i class="fa fa-video-camera"></i></span>
		|
		<span class="typeFilter" id="type_audio" rel="Audio"><i class="fa fa-music"></i></span>
		<span class="clear-both"></span>
	</div>
	<div class="vmodes">
		<div style="display:none;" class="vmode fa fa-list-alt <?php echo ($this->viewMode=='min'?'selectedFilter':'');?>" rel="min"></div>
		<div class="vmode fa fa-list <?php echo ($this->viewMode=='norm'?'selectedFilter':'');?>" rel="norm"></div>
		<div class="vmode fa fa-table <?php echo ($this->viewMode=='thumb'?'selectedFilter':'');?>" rel="thumb"></div>
		<div class="clear-both"></div>
	</div>
	<div class="searcharea" >
		<input type="text" id="libsearch" name="search" value="" placeholder="Search media" />
	</div>
	<div class="clear-both"></div>
</div>