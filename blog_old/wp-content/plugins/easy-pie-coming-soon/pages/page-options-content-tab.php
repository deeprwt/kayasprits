<?php


//- Settings Logic -
$action_updated = null;
$global = EZP_CS_Global_Entity::get_instance();
$set_index = $global->active_set_index;
$set = EZP_CS_Set_Entity::get_by_id($set_index);
$content = EZP_CS_Content_Entity::get_by_id($set->content_index);
$config = EZP_CS_Config_Entity::get_by_id($global->config_index);
$error_string = "";

if (isset($_POST['action']) && $_POST['action'] == 'save')
{
	check_admin_referer('easy-pie-coming-soon-save-content');
	// Artificially set the bools since they aren't part of the postback    
	// TODO
	$error_string = $content->set_post_variables($_POST);
	if ($error_string == "")
	{
		$action_updated = $content->save();
	}
}
?>
<script type="text/javascript">
    ezp_cs_datepicker_date_format = "<?php echo EZP_CS_Render_Utility::get_datepicker_date_format(); ?>";
</script>

<?php wp_nonce_field('easy-pie-coming-soon-save-content'); ?>
<input type="hidden" name="action" value="save"/>

<?php if ($error_string != "") :	?>
	<div id="message" class="error below-h2"><p><?php echo EZP_CS_Utility::__('Errors present:') . "<br/> $error_string" ?></p></div>
<?php endif; ?>

<?php if ($action_updated) : ?>
	<div id="message" class="updated below-h2">
		<p><span><?php echo EZP_CS_Utility::__('Settings Saved.'); ?></span><strong style="margin-left:7px;"><?php echo '  ' . EZP_CS_Utility::__('If you have a caching plugin be sure to clear it.'); ?></strong></p>
	</div>
<?php endif; ?>


<!-- ===================================
TITLE -->
<div class="postbox"  style="margin-top:15px;">
<div class="inside">
<h3 class="ezp-cspe-subtitle"><?php EZP_CS_Utility::_e("TITLE") ?></h3>
        <table class="form-table"> 
            <tr>
                <th scope="row"><?php echo EZP_CS_Utility::_e("Window Title") ?></th>
                <td>
					<input class="long-input" name="title" type="text" value="<?php EZP_CS_Utility::_he($content->title); ?>" /><br/>
					<i><?php echo EZP_CS_Utility::_e("Note: This is the page title of the window") ?></i>
                </td>
            </tr>   
        </table>
    </div>
</div>


<!-- ===================================
CONTENT -->
<div class="postbox">
<div class="inside">
<h3 class="ezp-cspe-subtitle"><?php EZP_CS_Utility::_e("CONTENT") ?></h3>

	<!-- MAIN TEXT -->
	<div class="ezp-cspe-subtitle2" style="margin-top:15px"><?php EZP_CS_Utility::_e("MAIN TEXT") ?></div>	
	<table class="form-table"> 
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Headline") ?></th>
			<td>
				<input class="long-input"  name="headline" type="text" value="<?php EZP_CS_Utility::_he($content->headline); ?>" />
			</td>
		</tr>   
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Description") ?></th>
			<td>
				<textarea rows="5" cols="67" name="description"><?php EZP_CS_Utility::_he($content->description); ?></textarea>
			</td>
		</tr>   
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Disclaimer") ?></th>
			<td>
				<input class="long-input"  name="disclaimer" type="text" value="<?php EZP_CS_Utility::_he($content->disclaimer); ?>" />              
			</td>
		</tr>               
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Footer") ?></th>
			<td>
				<input class="long-input" name="footer" type="text" value="<?php EZP_CS_Utility::_he($content->footer); ?>" />
			</td>
		</tr>      
	</table> 

	<!-- EMAIL TEXT -->
	<div class="ezp-cspe-subtitle2" style="margin-top:15px"><?php EZP_CS_Utility::_e("EMAIL TEXT") ?></div>	
	<table class="form-table"> 
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Email Placeholder") ?></th>
			<td>
				<input class="long-input"  name="email_placeholder_text" type="text" value="<?php EZP_CS_Utility::_he($content->email_placeholder_text); ?>" />
			</td>
		</tr>   
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Name Placeholder") ?></th>
			<td>
				<input class="long-input"  name="name_placeholder_text" type="text" value="<?php EZP_CS_Utility::_he($content->name_placeholder_text); ?>" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Button") ?></th>
			<td>
				<input name="email_button_text" type="text" value="<?php echo $content->email_button_text; ?>" />
			</td>
		</tr>       
	</table>
	<div style="margin-top:17px"><span class="description">
		<?php echo '*' . EZP_CS_Utility::__('Section relevant only if email collection is enabled in') . ' <a href="' . admin_url() . 'admin.php?page=' . EZP_CS_Constants::$SETTINGS_SUBMENU_SLUG . '">' . self::__('settings') . '</a>'; ?></span>
	</div>
		
</div>
</div>

<!-- ===================================
THANK YOU TEXT -->
<div class="postbox">
<div class="inside">
	<h3 class="ezp-cspe-subtitle"><?php EZP_CS_Utility::_e("Thank You Text") ?></h3>
	<table class="form-table"> 
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Headline") ?></th>
			<td><input class="long-input"  name="thank_you_headline" type="text" value="<?php EZP_CS_Utility::_he($content->thank_you_headline); ?>" /></td>
		</tr>
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Text") ?></th>
			<td><textarea rows="5" cols="67" name="thank_you_description"><?php EZP_CS_Utility::_he($content->thank_you_description); ?></textarea></td>
		</tr>
	</table>
	<div style="margin-top:17px">
		<span class="description"><?php echo '*' . EZP_CS_Utility::__('Section relevant only if email collection is enabled in') . ' <a href="' . admin_url() . 'admin.php?page=' . EZP_CS_Constants::$SETTINGS_SUBMENU_SLUG . '">' . self::__('settings') . '</a>'; ?></span>
	</div>
</div>
</div>

<!-- ===================================
COUNTDOWN TEXT -->
<div class="postbox">
<div class="inside">
	<h3 class="ezp-cspe-subtitle"><?php EZP_CS_Utility::_e("Countdown Text") ?></h3>
	<table class="form-table"> 
		<tr>
			<th scope="row"><?php echo EZP_CS_Utility::_e("Due Date") ?></th>
			<td>
				<input style="width:130px;" id="ezp-countdown-due-date" class="long-input" name="countdown_due_date" type="text" value="<?php EZP_CS_Utility::_he($content->countdown_due_date); ?>" />
				<div><span class="description"><?php EZP_CS_Utility::_e('Countdown timer will display when populated'); ?></span></div>
			</td>
		</tr>
		<tr>
			<td style="padding-left:0; padding-bottom:0;" colspan="2">
				<small>Auto disable page after countdown with <a style="color:#DC3232" href="https://snapcreek.com" target="_blank">Coming Soon Page Elite</a></small>
			</td>
		</tr>

	</table>
</div>
</div>
