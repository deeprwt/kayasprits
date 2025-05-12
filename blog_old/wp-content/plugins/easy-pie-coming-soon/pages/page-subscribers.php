<?php
if (isset($_GET['tab'])) {
    $active_tab = $_GET['tab'];
    if(($active_tab != '') && ($active_tab != 'list') && ($active_tab != 'newsletter'))
    {
        echo 'Invalid request';
        die();
    }
} else {
    $active_tab = 'list';
}
?>

<script type="text/javascript" src='<?php echo EZP_CS_Utility::$PLUGIN_URL . "/js/page-subscribers-$active_tab-tab.js?" . EZP_CS_Constants::PLUGIN_VERSION; ?>'></script>

<style lang="text/css">
    .compound-setting { line-height:20px;}
    .narrow-input { width:66px;}
    .long-input { width: 345px;}
</style>

<div class="wrap">

    <?php screen_icon(EZP_CS_Constants::PLUGIN_SLUG); ?>
    <h2><?php EZP_CS_Utility::_e('Subscriber Management'); ?></h2>
    <?php
    $global = EZP_CS_Global_Entity::get_instance();
    $config = EZP_CS_Config_Entity::get_by_id($global->config_index);
    ?>
    
    <div id="easypie-cs-options" class="inside">
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?php echo EZP_CS_Constants::$SUBSCRIBERS_SUBMENU_SLUG . '&tab=list' ?>" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>"><?php EZP_CS_Utility::_e('Subscribers'); ?></a>  
            <a href="?page=<?php echo EZP_CS_Constants::$SUBSCRIBERS_SUBMENU_SLUG . '&tab=newsletter' ?>" class="nav-tab <?php echo $active_tab == 'newsletter' ? 'nav-tab-active' : ''; ?>"><?php EZP_CS_Utility::_e('Create Newsletter'); ?></a>  
        </h2>
        <form id="easy-pie-cs-main-form" method="post" action="<?php echo admin_url('admin.php?page=' . EZP_CS_Constants::$SUBSCRIBERS_SUBMENU_SLUG . '&tab=' . $active_tab); ?>" > 
            <div id='tab-holder'>
                <?php
                if ($active_tab == 'list') {
                    include 'page-subscribers-list-tab.php';
                } 
                else
                {
                    include 'page-subscribers-newsletter-tab.php';
                } 
                ?>         
            </div>             
        
            <?php EZP_CS_Utility::echo_footer_links(); ?>
        </form>
    </div>
</div>

