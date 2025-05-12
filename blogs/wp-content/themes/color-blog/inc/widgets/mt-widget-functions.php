<?php
/**
 * custom function and work related to widgets.
 *
 * @package Mystery Themes
 * @subpackage Color Blog
 * @since 1.0.0
 */

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function color_blog_widgets_init() {
	/**
	 * Register default sidebar
	 *
	 * @since 1.0.0
	 */
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'color-blog' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'color-blog' ),

		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	/**
	 * Register Header Ads Section
	 *
	 * @since 1.0.0
	 */
	register_sidebar( array(
		'name'          => esc_html__( 'Header Ads Section', 'color-blog' ),
		'id'            => 'header-ads-section',
		'description'   => esc_html__( 'Add MT: Ads Banner widgets here.', 'color-blog' ),

		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	/**
	 * Register 4 different footer area 
	 *
	 * @since 1.0.0
	 */

	register_sidebars( 4 , array(
		'name'          => esc_html__( 'Footer %d', 'color-blog' ),
		'id'            => 'footer-sidebar',
		'description'   => esc_html__( 'Added widgets are display at Footer Widget Area.', 'color-blog' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	// Author Info
	register_widget( 'Color_Blog_Author_Info' );

	// Latest Posts
	register_widget( 'Color_Blog_Latest_Posts' );

	//Social Media
	register_widget( 'Color_Blog_Social_Media' );
}
add_action( 'widgets_init', 'color_blog_widgets_init' );

/*-----------------------------------------------------------------------------------------------------------------------*/
/**
 * Load widget required files
 *
 * @since 1.0.0
 */
require get_template_directory() . '/inc/widgets/mt-widget-fields.php';    // Widget fields
require get_template_directory() . '/inc/widgets/mt-author-info.php';    // Author Info
require get_template_directory() . '/inc/widgets/mt-latest-posts.php';    // Latest Posts
require get_template_directory() . '/inc/widgets/mt-social-media.php';    // Social Media