<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Mystery Themes
 * @subpackage Color Blog
 * @since 1.0.0
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php 
	/**
	 * color_blog before page hook 
	 * 
	 * @since 1.0.0
	 */
	do_action( 'color_blog_before_page' );
?>

<div id="page" class="site">
<a class="skip-link screen-reader-text" href="#content"><?php echo esc_html_e( 'Skip To Content', 'color-blog' ) ?></a>
	<?php
		/**
		 * color_blog before header
		 * 
		 * @since 1.0.0
		 */
		do_action( 'color_blog_before_header' );

		$color_blog_enable_top_header = get_theme_mod( 'color_blog_enable_top_header', true );
		if( true ===  $color_blog_enable_top_header ){
			/**
			 * hook - color_blog_top_header
			 * 
			 * @hooked - color_blog_top_header_start - 5
			 * @hooked - color_blog_trending_section - 10 
			 * @hooked - color_blog_top_header_nav - 20
			 * @hooked - color_blog_top_header_end - 50
			 */
			do_action( 'color_blog_top_header' );				
		}

		/**
		 * color_blog main header
		 * 
		 * @hooked - color_blog_main_header_start - 5
		 * @hooked - color_blog_site_branding - 10
		 * @hooked - color_blog_menu_wrapper_start - 15
		 * @hooked - color_blog_header_main_menu - 20
		 * @hooked - color_blog_menu_icon_wrapper_start - 25
		 * @hooked - color_blog_menu_social_icons - 30
		 * @hooked - color_blog_menu_search_icon - 35
		 * @hooked - color_blog_menu_icon_wrapper_end - 40
		 * @hooked - color_blog_menu_wrapper_end - 45
		 * @hooked - color_blog_main_header_end - 50
		 * 
		 * @since 1.0.0
		 */
		do_action( 'color_blog_main_header' );

		if( is_front_page() ){
			/**
			 * hook - front_slider_section
			 * displays front top section before archive blogs.
			 */
			do_action( 'color_blog_front_slider_section' );
		}

		if( ! is_front_page() ) {
            /**
    		 * color_blog_innerpage_header hook
    		 *
    		 * @hooked - color_blog_innerpage_header_start - 5
    		 * @hooked - color_blog_innerpage_header_title - 10
    		 * @hooked - color_blog_breadcrumb_content - 15
    		 * @hooked - color_blog_innerpage_header_end - 20
    		 *
    		 * @since 1.0.0
    		 */
    		do_action( 'color_blog_innerpage_header' );
        }
	?>

	<div id="content" class="site-content">
		<div class="mt-container">
