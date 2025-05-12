<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
  	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script type="text/javascript" src="<?php echo ML_ROOT_PATH?>js/core.js"></script>
		<script type="text/javascript" src="<?php echo ML_ROOT_PATH?>js/jquery.html5_upload.js"></script>
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<title>
			<?php echo 'Media Library - '.(is_numeric($this->userLoc) && $this->userLoc==0?'Admin':$this->userLoc); ?>
		</title>
		<link type="text/css" href="<?php echo ML_ROOT_PATH?>css/lib.css" rel="stylesheet">

                <script type="text/javascript">
		$(function() {
                    var upload_new = $("#upload_field"), drop = $("#drop-zone"), old_ies = $("#old_ies");                               		if(window.File){
                        old_ies.css("display", "none");
                        upload_new.css("display", "block");
                        drop.css("display", "block");
                    } else {
                        upload_new.css("display", "none");
                        drop.css("display", "none");
			old_ies.css("display", "inline-block");
                    }
                });
                </script>
		<link href="http://vjs.zencdn.net/4.3/video-js.css" rel="stylesheet">
		<script src="http://vjs.zencdn.net/4.3/video.js"></script>
	</head>
	<body>

		<div class="container">
			<div class="mediafolder">
				<?php echo 'Media Library (User: '.$folder->getLoggedUser().')';?>
			</div>
			<div class="fileuploader" id="fileuploader" style="display: <?php echo $folder->getLoggedUser()=='Guest'?'none':'block';?>;">
				<!--support for older IE browsers 9 > 8 > 7 > 6 -->
                <form id="old_ies" style="display:none;" method="post" action="<?php echo $folder->getRootFile().HREF_GLUE.'uploadFiles&loc='.$folder->getUserLoc().'&root='.$this->EZGRoot.'&media_type='.$folder->getMediaType();?>" enctype="multipart/form-data">
                <!--resize-->
				<input id="loc" type="hidden" value="<?php echo $this->userLoc;?>"/>
				<input id="media_type" type="hidden" value="<?php echo $folder->getMediaType();?>"/>
				<input id="__file__" type="hidden" value="<?php echo $folder->getRootFile(); ?>" />
				<input id="vmode" type="hidden" value="<?php echo $this->viewMode; ?>" />
				<input id="fldId" type="hidden" value="<?php echo $this->EZGId; ?>" />
				<input id="ezgRoot" type="hidden" value="<?php echo $this->EZGRoot;?>" />
                <!--resize-->
                    <input type="file" name="user_file[]"/>
                    <input type="submit"  value="Upload"/>
                </form>
				<input name="user_file[]" type="file" multiple="multiple" id="upload_field" rel="<?php echo $folder->getRootFile().HREF_GLUE.'uploadFiles&loc='.$folder->getUserLoc().'&root='.$this->EZGRoot.'&media_type='.$folder->getMediaType();?>"/>
				<div id="progress_report">
					<div id="progress_report_name"></div>
					<div id="progress_report_status" ></div>
					<progress id="progress_report_bar" min="0" max="100"></progress>
				</div>
				<div id='drop-zone'>
					or drop here!
				</div>
				<div id="resizeZone"<?php echo $this->resizeDisabled?' class="gone"':'';?>>
					<input id="resizeChecker" type="checkbox" name="resizeMediaFlag" value="resize"<?php echo !$this->resizeDisabled?' checked="checked"':'';?> />
					Resize to
					<input id="resizeVal" type="input" name="resizeMediaVal" value="<?php echo $this->defResize;?>" />
					px.
				</div>
			</div>
			<div class="video_container gone">
				<video id="MLVideo" class="video-js vjs-default-skin" controls
					preload="auto" width="640" height="360">
				</video>
			</div>
			<div class="image_container gone">
				<div><span class="close_container">X</span></div>
				<div class="image_area"></div>
			</div>
			<?php echo $folder->displayFilters(); ?>
			<div class="listheading">
				<div class="mediacheck"><input type="checkbox" id="checkall" name="checkall" value="checkall" /></div>
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
					<div class="mediafile <?php echo $i%2==0?'even':'odd'; ?>-row <?php echo $file->getFullName()==$recentlyAdded?'selectedData':''; ?>">
						<div class="mediacheck padded">
						<?php if($file->getName()!='..'){?>
							<input class="mediacheckbox" type="checkbox" name="media[]" value="<?php echo $file->getFullName();?>" />
								<?php } ?>
						</div>
						<a class="entryhref <?php echo $file->getFullName()==$recentlyAdded?'recent':''; ?>" target="_self" href="<?php echo $file->getFullPath();?>" rel="<?php echo $file->getEZGRoot();?>">
							<div class="mediadata">
								<div class="mediaimg">
								<?php
								if(preg_match('/(jpg|jpeg|png|bmp|gif)$/i',$file->getType()))
								{
									?>
										<img class="img-thumb" src="<?php echo $file->getFullPath(true); ?>" />
									<?php
								}
								elseif(preg_match('/(mp3|wav)$/i',$file->getType()))
								{
									$mediaRel=' rel="audio/'.$file->getType().'|'.$file->getFullPath().'"';
									?>
										<i class="fa fa-music fa-2x fa-fw playBtn"<?php echo $mediaRel; ?>></i>
									<?php
								}
								elseif(preg_match('/(avi|cam|flv|mov|mpeg|mpg|swf|wmv|mp4|ogg|webm)$/i',$file->getType()))
								{
									$mediaRel=' rel="video/'.$file->getType().'|'.$file->getFullPath().'"';
									?>
										<i class="fa fa-video-camera fa-2x fa-fw playBtn"<?php echo $mediaRel; ?>></i>
									<?php
								}
								elseif($file->getType()=='dir')
								{
									if($file->getName() == '..')
									{
									?>
										<span class="fa-stack fa-fw libview_stack">
											<i class="fa fa-folder-o fa-stack-2x fa-fw"></i>
											<i class="fa fa-level-up fa-stack-1x fa-fw libview_level_up"></i>
										</span>
									<?php
									}
									else
									{
									?>
										<i class="fa fa-folder-o fa-2x fa-fw"></i>
									<?php
									}
								}
								else
								{
									?>
										<i class="fa fa-file-text-o fa-2x fa-fw"></i>
									<?php
								}
								?>
								</div>
								<div class="mediatext" >
									<?php echo $file->getFullName(TRUE);
									if($file->getName() != '..')
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
						<div class="mediastatus"><?php echo $file->getModedDate();?></div>
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
				<?php echo $folder->displayNav(); ?>
				<div class="clear-both"></div>
			</div>
		</div>
	</body>
</html>
