<!doctype html>
<html>
	<head>
		<script type="text/javascript" src="<?php echo ML_ROOT_PATH?>js/jquery-2.0.3.min.js"></script>
		<script type="text/javascript" src="<?php echo ML_ROOT_PATH?>js/core.js"></script>
		<script type="text/javascript" src="<?php echo ML_ROOT_PATH?>js/jquery.html5_upload.js"></script>
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<title>
			<?php echo 'Media Library - '.(is_numeric($this->userLoc)&&$this->userLoc==0?'Admin':$this->userLoc);?>
		</title>
		<link type="text/css" href="<?php echo ML_ROOT_PATH?>css/lib.css" rel="stylesheet">
		<link type="text/css" href="<?php echo ML_ROOT_PATH?>css/filters.css" rel="stylesheet">
		<link type="text/css" href="<?php echo ML_ROOT_PATH?>css/nav.css" rel="stylesheet">
		<link href="http://vjs.zencdn.net/4.3/video-js.css" rel="stylesheet">
		<script src="http://vjs.zencdn.net/4.3/video.js"></script>
	</head>
	<body>
		<input id="loc" type="hidden" value="<?php echo $this->userLoc;?>"/>
		<input id="media_type" type="hidden" value="<?php echo $folder->getMediaType();?>"/>
		<input id="__file__" type="hidden" value="<?php echo $folder->getRootFile();?>" />
		<input id="vmode" type="hidden" value="<?php echo $this->viewMode;?>" />
		<div class="container">
			<div class="mediafolder">
				<?php echo 'Media Library (User: '.$folder->getLoggedUser().')';?>
			</div>
			<div class="fileuploader" id="fileuploader" style="display: <?php echo $folder->getLoggedUser()=='Guest'?'none':'block';?>;">
				<input name="user_file[]" type="file" multiple="multiple" id="upload_field" rel="<?php echo $folder->getRootFile().HREF_GLUE;?>uploadFiles&loc=<?php echo $folder->getUserLoc();?>"/>
				<div id="progress_report">
					<div id="progress_report_name"></div>
					<div id="progress_report_status" ></div>
					<progress id="progress_report_bar" min="0" max="100"></progress>
				</div>
				<div id='drop-zone'>
					or drop here!
				</div>
				<input id="resizeChecker" type="checkbox" name="resizeMediaFlag" value="resize" checked="checked" />
				Resize to
				<input id="resizeVal" type="input" name="resizeMediaVal" value="<?php echo $this->defResize;?>" />
				px.
			</div>

			<?php echo $folder->displayFilters();?>
			<div class="listheading">
				<div class="mediacheck"><input type="checkbox" id="checkall" name="checkall" value="checkall" /></div>
				<div class="mediadata">Title/Type</div>
				<div class="mediastatus">File Size</div>
				<div class="mediastatus">File Date</div>
				<div class="clear-both"></div>
			</div>
			<div class="fileslist">
				<?php
				$i=0;
				$recentlyAdded = $folder->getRecentlyAdded();
				foreach($files as $file)
				{
					$i++;
					?>
					<div class="mediafile <?php echo $i%2==0?'even':'odd';?>-row">
						<div class="mediacheck">
						<?php if($file->getName()!='..'){?>
							<input class="mediacheckbox" type="checkbox" name="media[]" value="<?php echo $file->getFullName();?>" />
						<?php } ?>
						</div>
						<a class="entryhref <?php echo $file->getFullName()==$recentlyAdded?'recent':''; ?>" target="_self" href="<?php echo $file->getFullPath();?>" rel="<?php echo $file->getEZGRoot();?>">
							<div class="mediadata">
								<div class="mediaimg-min">
									<?php
									if(preg_match('/(jpg|jpeg|png|bmp|gif)$/i',$file->getType()))
									{
										?>
										<img class="img-thumb-min" src="<?php echo $file->getFullPath(true);?>" />
										<?php
									}
									elseif(preg_match('/(mp3|wav)$/i',$file->getType()))
									{
										?>
										<i class="fa fa-music fa-fw"></i>
										<?php
									}
									elseif(preg_match('/(avi|cam|flv|mov|mpeg|mpg|swf|wmv|mp4|ogg|webm)$/i',$file->getType()))
									{
										?>
										<i class="fa fa-video-camera fa-fw"></i>
										<?php
									}
									elseif($file->getType()=='dir')
									{
										if($file->getName()=='..')
										{
											?>
											<span class="fa-stack fa-fw libview_stack-min">
												<i class="fa fa-folder-o fa-stack-1x fa-fw libview_folder-min"></i>
												<i class="fa fa-level-up fa-stack-1x fa-fw libview_level_up-min"></i>
											</span>
											<?php
										}
										else
										{
											?>
											<i class="fa fa-folder-o fa-fw"></i>
											<?php
										}
									}
									else
									{
										?>
										<i class="fa fa-file-text-o fa-fw"></i>
										<?php
									}
									?>
								</div>
								<div class="mediatext" >
									<?php
									echo $file->getName().($file->getType()=='dir'?'':'<div style="float:right">('.$file->getType().')</div>');
									if($file->getName()!='..')
									{
										?>
										<div class="entrynav hidden" >
											[<span class="entrynavElem delBtn" >Delete</span>]
										</div>
										<?php
									}
									?>
								</div>
								<div class="clear-both"></div>
							</div>
						</a>
						<div class="mediastatus"><?php echo $file->getSize(true);?></div>
						<div class="mediastatus"><?php echo $file->getModedDate('m.d.y G:i');?></div>
						<div class="clear-both"></div>
					</div>
					<?php
				}
				?>
				<div class="clear-both"></div>
			</div>
			<div class="listfooter" >
				<button class="delete_btn">Delete</button>
				<button class="addFldr_btn">Add folder</button>
<?php echo $folder->displayNav();?>
				<div class="clear-both"></div>
			</div>
		</div>
	</body>

</html>
