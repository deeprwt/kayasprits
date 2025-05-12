<?php
/**
 * Color Blog manage the Customizer panels
 *
 * @package Mystery Themes
 * @subpackage Color Blog
 * @since 1.0.0
 */
add_action( 'customize_register', 'color_blog_customize_panels_register' );

/**
 * Add panels in the theme cutomizer
 * 
 */
function color_blog_customize_panels_register( $wp_customize ){
	/**
	 * General Settings Panel
	 */
	$wp_customize->add_panel( 'color_blog_general_panel',
		array(
			'priority'          => 10,
			'capability'        => 'edit_theme_options',
			'theme_supports'    => '',
			'title'             => __( 'General Settings', 'color-blog' ),
		)
	);

	/**
	 * Header Settings Panel
	 */
	$wp_customize->add_panel( 'color_blog_header_panel',
		array(
			'priority'          => 15,
			'capability'        => 'edit_theme_options',
			'theme_supports'    => '',
			'title'             => __( 'Header Settings', 'color-blog' ),
		)
	);

	/**
	 * Front Settings Panel
	 */
	$wp_customize->add_panel( 'color_blog_front_section_panel',
		array(
			'priority'          => 20,
			'capability'        => 'edit_theme_options',
			'theme_supports'    => '',
			'title'             => __( 'Front Sections', 'color-blog' ),
		)
	);

	/**
	 * Design Settings Panel
	 */
	$wp_customize->add_panel( 'color_blog_design_panel',
		array(
			'priority'          => 35,
			'capability'        => 'edit_theme_options',
			'theme_supports'    => '',
			'title'             => __( 'Design Settings', 'color-blog' ),
		)
	);
	
	/**
	 * Additional Features Panel
	 */
	$wp_customize->add_panel( 'color_blog_additional_panel',
		array(
			'priority'          => 40,
			'capability'        => 'edit_theme_options',
			'theme_supports'    => '',
			'title'             => __( 'Additional Features', 'color-blog' ),
		)
	);

	/**
	 * Footer Settings Panel
	 */
	$wp_customize->add_panel( 'color_blog_footer_panel',
		array(
			'priority'          => 45,
			'capability'        => 'edit_theme_options',
			'theme_supports'    => '',
			'title'             => __( 'Footer Settings', 'color-blog' ),
		)
	);
}