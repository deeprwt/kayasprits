<?php

if (isset($_GET['tab'])) {

    $active_tab = $_GET['tab'];

    if((trim($active_tab) != '') && ($active_tab != 'display') && ($active_tab != 'content') && ($active_tab != 'preview')) {
        echo 'Invalid request';
        die();
    }
} else {

    $active_tab = 'display';
}
?>
<script type="text/javascript">
	easyPie = {};
	easyPie.CS = {};
	easyPie.CS.Options = {};
</script>

<script type="text/javascript" src='<?php echo EZP_CS_Utility::$PLUGIN_URL . "/js/page-options-$active_tab-tab.js?" . EZP_CS_Constants::PLUGIN_VERSION; ?>'></script>

<style lang="text/css">
    .compound-setting { line-height:20px;}
    .narrow-input { width:66px;}
    .long-input { width: 345px;}
	.postbox .inside {margin-bottom: 6px}
	.form-table th{padding: 8px 8px 8px 25px}
	.form-table td{padding: 3px 0 3px 0}
</style>

<div class="wrap">

    <?php screen_icon(EZP_CS_Constants::PLUGIN_SLUG); ?>
    <h2><?php EZP_CS_Utility::_e('Template'); ?></h2>
    <?php
    if (isset($_GET['settings-updated'])) {
        echo "<div class='updated'><p>" . EZP_CS_Utility::__('If you have a caching plugin, be sure to clear the cache!') . "</p></div>";
    }
    
    $global = EZP_CS_Global_Entity::get_instance();
    $config = EZP_CS_Config_Entity::get_by_id($global->config_index);

    ?>
    
    <div id="easypie-cs-options" class="inside">
        <h2 class="nav-tab-wrapper">  
            <a href="?page=<?php echo EZP_CS_Constants::PLUGIN_SLUG . '&tab=display' ?>" class="nav-tab <?php echo $active_tab == 'display' ? 'nav-tab-active' : ''; ?>"><?php EZP_CS_Utility::_e('Style'); ?></a>  
            <a href="?page=<?php echo EZP_CS_Constants::PLUGIN_SLUG . '&tab=content' ?>" class="nav-tab <?php echo $active_tab == 'content' ? 'nav-tab-active' : ''; ?>"><?php EZP_CS_Utility::_e('Text'); ?></a>  
            <a href="?page=<?php echo EZP_CS_Constants::PLUGIN_SLUG . '&tab=preview' ?>" class="nav-tab <?php echo $active_tab == 'preview' ? 'nav-tab-active' : ''; ?>"><?php EZP_CS_Utility::_e('Preview'); ?></a>  
        </h2>
        <form id="easy-pie-cs-main-form" method="post" action="<?php echo admin_url('admin.php?page=' . EZP_CS_Constants::PLUGIN_SLUG . '&tab=' . $active_tab); ?>" >    
            <div id='tab-holder'>
                <?php
                if ($active_tab == 'display') {
                    include 'page-options-display-tab.php';
                } else if ($active_tab == 'content') {
                    include 'page-options-content-tab.php';
                } else {
                    include 'page-preview-tab.php';
                }
                                
                if (isset($_POST['ezp-cs-submit-type']) && ($_POST['ezp-cs-submit-type'] == 'preview') ){
                    
                    $preview_url = '?page=' . EZP_CS_Constants::PLUGIN_SLUG . '&tab=preview';
                        
                    echo '<script>'                    
                        . 'window.location="' . $preview_url . '";'
                        . '</script>';                                                                 
                }
                
                ?>         
                <!-- after redirect -->
            </div>           

            <input type="hidden" id="ezp-cs-submit-type" name="ezp-cs-submit-type" value="save"/>
            
            <p>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />&nbsp;
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save & Preview" onclick="document.getElementById('ezp-cs-submit-type').value = 'preview';debugger;return true;"/>
            </p>                

            <a href="https://snapcreek.com/ezp-coming-soon/docs/faqs-tech/" target="_blank"><?php EZP_CS_Utility::_e('FAQ'); ?></a> |
            <a href="https://wordpress.org/support/plugin/easy-pie-coming-soon/reviews/" target="_blank"><?php echo EZP_CS_Utility::__('Rate'); ?></a> |            
            <a href="https://snapcreek.com/support/" target="_blank"><?php EZP_CS_Utility::_e('Contact') ?></a>
        </form>
    </div>
</div>

