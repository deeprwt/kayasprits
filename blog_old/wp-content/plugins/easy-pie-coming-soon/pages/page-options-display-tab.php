<?php
$action_updated = null;
$global = EZP_CS_Global_Entity::get_instance();
$set_index = $global->active_set_index;
$set = EZP_CS_Set_Entity::get_by_id($set_index);
$display = EZP_CS_Display_Entity::get_by_id($set->display_index);
$content = EZP_CS_Content_Entity::get_by_id($set->content_index);
$config = EZP_CS_Config_Entity::get_by_id($global->config_index);
$error_string = "";

if (isset($_POST['action']) && $_POST['action'] == 'save')
{
	check_admin_referer('easy-pie-coming-soon-save-display');
	EZP_CS_Utility::debug('past admin check');

	// Artificially set the bools since they aren't part of the postback
	$display->background_tiling_enabled = "false";
	$error_string = $display->set_post_variables($_POST);

	if ($error_string == "")
	{
		$action_updated = $display->save();
	}
	$content->logo_url = $_POST['logo_url'];
	$content->save();
}
?>

<style>    
    #easy-pie-cs-advanced h3 { cursor: default; margin-left:5px; margin-top:10px;}
    #easy-pie-cs-advanced table { margin-left:10px;}    
    #easy-pie-cs-quick-config-div { margin-top: 10px; width: 670px; overflow:hidden;}

    .template-image-holder { margin: 10px 10px 15px 10px; }
    .template-image { width:200px; opacity:0.4; border: black 1px solid;}
    .template-name { }
    .template-button { margin-top: 10px!important; }
    #easy-pie-cs-template-cancel-btn { }
    #easy-pie-cs-template-copy-btn { float:right;}    
    #template-dialog {background-color:white; box-shadow: 1px 7px 36px -5px rgba(34,34,34,1); border: #777 1px solid; padding:13px}
    #opacity-slider { margin-left:40px; width: 130px; }
    #opacity-slider .ui-slider-handle { width: 8px; text-align: center; }

    .ezp-cs-radiodiv { margin-bottom:10px;}

    #easy-pie-cs-builtin-background-slider { width: 610px}
    #easy-pie-cs-builtin-background-slider img { height:100px; width:100px; margin-right:10px; margin-top:7px;}

	.ezp-cs-color-row { <?php EZP_CS_Utility::echo_display($display->background_type == EZP_CS_Display_Background_Type::Color, 'table-row', 'none'); ?> }
	.ezp-cs-image-row { <?php EZP_CS_Utility::echo_display($display->background_type == EZP_CS_Display_Background_Type::Image, 'table-row', 'none'); ?> }
	.ezp-cs-video-row { <?php EZP_CS_Utility::echo_display($display->background_type == EZP_CS_Display_Background_Type::Video, 'table-row', 'none'); ?> }
</style>


<?php wp_nonce_field('easy-pie-coming-soon-save-display'); ?>
<input type="hidden" name="action" value="save"/>
<?php if ($error_string != "") :?>
	<div id="message" class="error below-h2">
		<p><?php echo EZP_CS_Utility::__('Errors present:') . "<br/> $error_string" ?></p>
	</div>
<?php endif; ?>

<?php if ($action_updated) : ?>
	<div id="message" class="updated below-h2">
		<p><span><?php echo EZP_CS_Utility::__('Settings Saved.'); ?></span><strong style="margin-left:7px;"><?php echo '  ' . EZP_CS_Utility::__('If you have a caching plugin be sure to clear it.'); ?></strong></p>
	</div>
<?php endif; ?>


<!-- ===================================
BACKGROUND -->
<div class="postbox"  style="margin-top:10px;">
<div class="inside">
	<h3 class="ezp-cspe-subtitle"><?php EZP_CS_Utility::_e("Background") ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Type") ?></th>
			<td>
				<select id="ezp-cs-background-type" name="background_type" onchange="easyPie.CS.Options.Display.ShowBackgroundSection();">
					<option <?php EZP_CS_Utility::echo_selected($display->background_type == EZP_CS_Display_Background_Type::Color) ?> value="<?php echo EZP_CS_Display_Background_Type::Color ?>"><?php EZP_CS_Utility::_e('Color'); ?></option>
					<option <?php EZP_CS_Utility::echo_selected($display->background_type == EZP_CS_Display_Background_Type::Image) ?> value="<?php echo EZP_CS_Display_Background_Type::Image ?>"><?php EZP_CS_Utility::_e('Image'); ?></option>
					<option <?php EZP_CS_Utility::echo_selected($display->background_type == EZP_CS_Display_Background_Type::Video) ?> value="<?php echo EZP_CS_Display_Background_Type::Video ?>"><?php EZP_CS_Utility::_e('Video'); ?></option>
				</select>
			</td>
		</tr>
		<tr class="ezp-cs-color-row">
			<th scope="row"><?php echo EZP_CS_Utility::_e("Color") ?></th>
			<td><input name="background_color" class="spectrum-picker" type="text" value="<?php echo $display->background_color; ?>"/></td>
		</tr>
		<tr class="ezp-cs-image-row">
			<th scope="row"><?php echo EZP_CS_Utility::_e("Image") ?></th>                
			<td>   
				<div class="ezp-cs-radiodiv">
					<div id="easy-pie-cs-builtin-background-slider"> 
						<?php
						$background_dir = EZP_CS_Utility::$PLUGIN_DIRECTORY . '/images/backgrounds/';
						$file_paths = glob($background_dir . '*.{jpg,png}', GLOB_BRACE);

						if ($file_paths != FALSE)
						{
							sort($file_paths);
							$image_index = 0;
							$build_in_background = false;
							foreach ($file_paths as $file_path)
							{
								$image_id = "built-in-bg-image-$image_index";
								$file_name = basename($file_path);
								$file_url = EZP_CS_Utility::$PLUGIN_URL . "/images/backgrounds/$file_name";

								if ($display->background_image_url != $file_url)
								{
									$opacity = 0.3;
								}
								else
								{
									$opacity = 1.0;
									$build_in_background = true;
								}
								echo "<img id='$image_id' src='$file_url' style='opacity:$opacity;cursor: pointer;'/>";
								$image_index++;
							}
						}
						?>
					</div>
				</div>
				<div class="ezp-cs-radiodiv">                                            
					<span class="description"><?php echo EZP_CS_Utility::__('or use your own') . ':'; ?></span><br/>
					<input id="easy-pie-cs-background-image-url" name="background_image_url" value="<?php echo $display->background_image_url; ?>"  style="width:500px" />                            
					<button id="easy-pie-cs-background-image-button"><?php EZP_CS_Utility::_e("Upload"); ?></button>
					<img id="easy-pie-cs-background-image-preview" style="display: <?php echo ($build_in_background || $display->background_image_url == '') ? 'none' : 'block' ?> ;width:80px;height:80px;margin-top:8px;" src="<?php echo $display->background_image_url; ?>" />
				</div>
			</td>
		</tr>            
		<tr class="ezp-cs-image-row">
			<th scope="row"><?php echo EZP_CS_Utility::_e("Tile") ?></th>
			<td>
				<input type="checkbox" name="background_tiling_enabled" value="true" <?php echo $display->background_tiling_enabled == 'true' ? 'checked' : ''; ?> />
				<span><?php EZP_CS_Utility::_e("Enabled") ?></span>                        
				<span class="description"><?php echo EZP_CS_Utility::__('Image covers screen when tiling is disabled') . '.'; ?></span>
			</td>
		</tr>
		<tr class="ezp-cs-video-row" style="color:silver">
			<th scope="row"><?php echo EZP_CS_Utility::_e("YouTube ID") ?></th>
			<td>
				<input disabled name="youtube_video_id" type="text" placeholder="<?php echo EZP_CS_Utility::_e("Available in Elite") ?>" />  <i class="fa fa-youtube fa-2x" aria-hidden="true"></i> 
				<span class="description"><?php echo sprintf('YouTube <a href="%s" target="blank">Video ID.</a>', 'http://docs.joeworkman.net/rapidweaver/stacks/youtube/video-id'); ?> </span>
			</td>
		</tr>   
		<tr class="ezp-cs-video-row">
			<th scope="row"><?php echo EZP_CS_Utility::_e("Sound") ?></th>
			<td>
				<input disabled type="checkbox" name="mute_background_video" value="true" checked />
				<span style="color:silver"><?php EZP_CS_Utility::_e("Mute") ?></span>  
				<br/><br/>
				<small>YouTube Backgrounds available with <a style="color:#DC3232" href="https://snapcreek.com/ezp-coming-soon/" target="_blank">Coming Soon Page Elite</a></small>
			</td>
		</tr>  
	</table>
</div>
</div>
		
		

<!-- ===================================
CONTENT -->
<div class="postbox">
<div class="inside">
	<h3 class="ezp-cspe-subtitle"><?php EZP_CS_Utility::_e("Content") ?></h3>
	
	<!-- CONTENT BOX -->
	<div class="ezp-cspe-subtitle2" style="margin-top:15px"><?php EZP_CS_Utility::_e("CONTENT BOX") ?></div>	
	<table class="form-table">
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Opacity") ?></th>
			<td>
				<div style="display:none;"><input class='narrow-input' id="content_box_opacity" name="content_box_opacity" type="text" value="<?php echo $display->content_box_opacity ?>" readonly="true"/>                        </div> 
				<div id="opacity-display-value" style="float:left;">hi</div>
				<div style="padding-top:2px;"><div id="opacity-slider"></div></div>                        
			</td>
		</tr>    
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Color") ?></th>
			<td>
				<input name="content_box_color" class="spectrum-picker" type="text" value="<?php echo $display->content_box_color ?>"/>                   
			</td>
		</tr>  
	</table>	
	
	<!-- LOGO-->
	<div class="ezp-cspe-subtitle2" style="margin-top:15px"><?php EZP_CS_Utility::_e("LOGO") ?></div>	
	<table class="form-table">    
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Image") ?></th>
			<td>
				<input id="easy-pie-cs-logo-url" name="logo_url" value="<?php echo $content->logo_url; ?>" />                            
				<button id="easy-pie-cs-logo-button"><?php EZP_CS_Utility::_e("Upload"); ?></button>
				<img id="easy-pie-cs-logo-preview" style="display: <?php echo $content->logo_url == '' ? 'none' : 'block' ?> ;width:80px;height:80px;margin-top:8px;" src="<?php echo $content->logo_url; ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Width") ?></th>
		<td>
				<input class='narrow-input' name="logo_width" type="text" value="<?php echo $display->logo_width; ?>" />
				<span class="description"><?php echo '*' . EZP_CS_Utility::__('Append px or %'); ?></span>
			</td>
		</tr>   
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Height") ?></th>
			<td>
				<input class='narrow-input' name="logo_height" type="text" value="<?php echo $display->logo_height; ?>" />
				<span class="description"><?php echo '*' . EZP_CS_Utility::__('Append px or %'); ?></span>
			</td>
		</tr>   
	</table>

	<!-- TEXT-->
	<div class="ezp-cspe-subtitle2" style="margin-top:15px"><?php EZP_CS_Utility::_e("TEXT") ?></div>	
	<table class="form-table">
		<tr>   
			<?php EZP_CS_Display_Entity::display_font_field_row('Headline', 'text_headline', $display) ?>
		</tr>
		<tr>
			<?php EZP_CS_Display_Entity::display_font_field_row('Description', 'text_description', $display) ?>
		</tr>

		<tr>
			<?php EZP_CS_Display_Entity::display_font_field_row('Disclaimer', 'text_disclaimer', $display) ?>
		</tr>
		<tr>
			<?php EZP_CS_Display_Entity::display_font_field_row('Footer', 'text_footer', $display) ?>
		</tr>          
		<tr>
			<th></th>
			<td>					
				<span class="description"><?php echo '*' . EZP_CS_Utility::__('Append px, rem or em for sizes'); ?></span>
			</td>
		</tr>
	</table>

	
	<!-- EMAIL BUTTON -->
	<div class="ezp-cspe-subtitle2" style="margin-top:15px"><?php EZP_CS_Utility::_e("EMAIL BUTTON") ?></div>
        <table class="form-table">
            <tr>
                <th scope="row"><?php echo EZP_CS_Utility::_e("Color") ?></th>
                <td>
                    <input name="email_button_color" class="spectrum-picker" type="text" value="<?php echo $display->email_button_color; ?>"/>
                </td>
            </tr>  			
            <tr>
                <th scope="row"><?php echo EZP_CS_Utility::_e("Width") ?></th>
                <td>
					<input class='narrow-input' name="email_button_width" type="text" value="<?php echo $display->email_button_width; ?>" />
					<span class="description"><?php echo '*' . EZP_CS_Utility::__('Append px or %'); ?></span>
                </td>
            </tr>   
            <tr>
                <th scope="row"><?php echo EZP_CS_Utility::_e("Height") ?></th>
                <td>
					<input class='narrow-input' name="email_button_height" type="text" value="<?php echo $display->email_button_height; ?>" />
					<span class="description"><?php echo '*' . EZP_CS_Utility::__('Append px or %'); ?></span>
                </td>
            </tr>  
            <tr><?php echo EZP_CS_Display_Entity::display_font_field_row("Font", 'email_button', $display); ?></tr>
            <tr>
				<?php echo EZP_CS_Display_Entity::display_font_field_row("Font", 'email_button', $display); ?>
            </tr>
			<tr>
				<td colspan="2" style="padding:30px 0 0 10px">
					<small>Google Fonts & Effects available in <a style="color:#DC3232" href="https://snapcreek.com/ezp-coming-soon/" target="_blank">Coming Soon Page Elite</a></small>
				</td>
			</tr>			
        </table>                       
    </div>
</div>


<!-- ===================================
ADVANCED -->
<div class="postbox">
<div class="inside">
	<h3 class="ezp-cspe-subtitle"><?php EZP_CS_Utility::_e("Advanced") ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php EZP_CS_Utility::_e("Custom CSS") ?></th><td>
				<textarea cols="75" rows="12" id="easy-pie-cs-field-junk" name="css" style="font-size:12px"><?php echo $display->css; ?></textarea>
			</td>
		</tr>
	</table>
	<div style="margin-top:4px;">
		<a target="_blank" href="https://snapcreek.com/ezp-coming-soon/docs/faqs-tech/"><span class="description"><?php EZP_CS_Utility::_e('CSS Styling Tips'); ?></span></a>
	</div>
</div>
</div>


<script type='text/javascript'>
	jQuery(document).ready(function ($)
	{                            
		easyPie.CS.Options.Display = {};
		easyPie.CS.Options.Display.ShowBackgroundSection = function () {

			var backgroundType = $("#ezp-cs-background-type").val();
			if (backgroundType == <?php echo EZP_CS_Display_Background_Type::Color ?>) {

				$('.ezp-cs-color-row').show();
				$('.ezp-cs-image-row').hide();
				$('.ezp-cs-video-row').hide();
			} else if (backgroundType == <?php echo EZP_CS_Display_Background_Type::Image ?>) {

				$('.ezp-cs-color-row').hide();
				$('.ezp-cs-image-row').show();
				$('.ezp-cs-video-row').hide();
			} else {

				$('.ezp-cs-color-row').hide();
				$('.ezp-cs-image-row').hide();
				$('.ezp-cs-video-row').show();
			}
		}
	});
</script>
