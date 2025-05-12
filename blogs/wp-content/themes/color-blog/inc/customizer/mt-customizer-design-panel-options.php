<?php
/**
 * Color Blog manage the Customizer options of design settings panel.
 *
 * @package Mystery Themes
 * @subpackage Color Blog
 * @since 1.0.0
 */

add_action( 'customize_register', 'color_blog_customize_design_panels_sections_register' );
/**
 * Add Additional panels in the theme cutomizer
 * 
 */

function color_blog_customize_design_panels_sections_register( $wp_customize ){
	/*------------------------------------------------ Archive Section ------------------------------------------------------------*/
	/**
	 * Archive Settings
	 */
	$wp_customize->add_section( 'color_blog_section_archive_settings',
		array(
			'title'    => esc_html__( 'Archive Settings', 'color-blog' ),
			'panel'          => 'color_blog_design_panel',
			'capability'     => 'edit_theme_options',
			'priority'       => 5,
			'theme_supports' => '',
		)
	);

	/*
	 * Radio Image field for archive/blog sidebar layout.
	 */
	$wp_customize->add_setting( 'color_blog_archive_sidebar_layout',
		array(
			'default'           => 'no-sidebar',
			'sanitize_callback' => 'sanitize_key',
		)
	);
	$wp_customize->add_control( new Color_Blog_Control_Radio_Image(
		$wp_customize, 'color_blog_archive_sidebar_layout',
			array(
				'label'    => esc_html__( 'Archive/Blog Sidebar Layout', 'color-blog' ),
				'section'       => 'color_blog_section_archive_settings',
				'settings'      => 'color_blog_archive_sidebar_layout',
				'priority'      => 10,
				'choices'  => array(
					'left-sidebar'  	 => get_template_directory_uri() . '/assets/images/left-sidebar.png',
					'right-sidebar' 	 => get_template_directory_uri() . '/assets/images/right-sidebar.png',
					'no-sidebar'         => get_template_directory_uri() . '/assets/images/no-sidebar.png',
					'no-sidebar-center'  => get_template_directory_uri() . '/assets/images/no-sidebar-center.png'
				),
			)
		)
	);

	/*
	 * Radio Image field for arvhive/blog style.
	 */
	$wp_customize->add_setting( 'color_blog_archive_style',
		array(
			'default'           => 'mt-archive--masonry-style',
			'sanitize_callback' => 'sanitize_key',
		)
	);
	$wp_customize->add_control( new Color_Blog_Control_Radio_Image(
		$wp_customize, 'color_blog_archive_style',
			array(
				'label'    => esc_html__( 'Archive/Blog Style', 'color-blog' ),
				'section'       => 'color_blog_section_archive_settings',
				'settings'      => 'color_blog_archive_style',
				'priority'      => 10,
				'choices'  => array(
					'mt-archive--block-grid-style' => get_template_directory_uri() . '/assets/images/archive-block-grid.png',
					'mt-archive--masonry-style'    => get_template_directory_uri() . '/assets/images/archive-masonry.png',
				),
			)
		)
	);

	/*
	 * Text field for archive read more button.
	 */
	$wp_customize->add_setting( 'color_blog_archive_read_more',
		array(
			'capability'        => 'edit_theme_options',
			'default'  => esc_html__( 'Discover', 'color-blog' ),
			'sanitize_callback' => 'sanitize_text_field'
		)
	);

	$wp_customize->add_control( 'color_blog_archive_read_more', 
		array(
			'type'			=> 'text',
			'label'    => esc_html__( 'Read More Button', 'color-blog' ),
			'section'       => 'color_blog_section_archive_settings',
			'priority'      => 15,
		)
	);

	/*------------------------------------------------------- Post Section ------------------------------------------------------------*/
	/**
	 * Post Settings
	 */
	$wp_customize->add_section( 'color_blog_section_post_settings',
		array(
			'title'    => esc_html__( 'Post Settings', 'color-blog' ),
			'panel'          => 'color_blog_design_panel',
			'capability'     => 'edit_theme_options',
			'priority'       => 10,
			'theme_supports' => '',
		)
	);
	/*
	* Radio Image field for single posts sidebar layout.
	*/
	$wp_customize->add_setting( 'color_blog_posts_sidebar_layout',
		array(
			'default'           => 'right-sidebar',
			'sanitize_callback' => 'sanitize_key',
		)
	);
	$wp_customize->add_control( new Color_Blog_Control_Radio_Image(
		$wp_customize, 'color_blog_posts_sidebar_layout',
			array(
				'label'    => esc_html__( 'Posts Sidebar Layout', 'color-blog' ),
				'section'       => 'color_blog_section_post_settings',
				'settings'      => 'color_blog_posts_sidebar_layout',
				'priority'      => 5,
				'choices'  => array(
					'left-sidebar'  	 => get_template_directory_uri() . '/assets/images/left-sidebar.png',
					'right-sidebar' 	 => get_template_directory_uri() . '/assets/images/right-sidebar.png',
					'no-sidebar'         => get_template_directory_uri() . '/assets/images/no-sidebar.png',
					'no-sidebar-center'  => get_template_directory_uri() . '/assets/images/no-sidebar-center.png'
				),
			)
		)
	);

	/*
	* Toggle field for Enable/Disable related posts.
	*/
	$wp_customize->add_setting( 'color_blog_enable_related_posts',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_related_posts',
			array(
				'label'    => esc_html__( 'Enable Related Posts', 'color-blog' ),
				'section'       => 'color_blog_section_post_settings',
				'settings'      => 'color_blog_enable_related_posts',
				'priority'      => 15,
			)
		)
	);

	/*------------------------------------------------------- Post Section ------------------------------------------------------------*/
	/**
	 * Page Setting
	 */
	$wp_customize->add_section( 'color_blog_section_page_settings',
		array(
			'title'    => esc_html__( 'Page Settings', 'color-blog' ),
			'panel'          => 'color_blog_design_panel',
			'capability'     => 'edit_theme_options',
			'priority'       => 15,
			'theme_supports' => '',
		)
	);

	/*
	* Radio Image field for single page sidebar layout.
	*/
	$wp_customize->add_setting( 'color_blog_pages_sidebar_layout',
		array(
			'default'           => 'right-sidebar',
			'sanitize_callback' => 'sanitize_key',
		)
	);
	$wp_customize->add_control( new Color_Blog_Control_Radio_Image(
		$wp_customize, 'color_blog_pages_sidebar_layout',
			array(
				'label'    => esc_html__( 'Pages Sidebar Layout', 'color-blog' ),
				'section'       => 'color_blog_section_page_settings',
				'settings'      => 'color_blog_pages_sidebar_layout',
				'priority'      => 5,
				'choices'  => array(
					'left-sidebar'  	 => get_template_directory_uri() . '/assets/images/left-sidebar.png',
					'right-sidebar' 	 => get_template_directory_uri() . '/assets/images/right-sidebar.png',
					'no-sidebar'         => get_template_directory_uri() . '/assets/images/no-sidebar.png',
					'no-sidebar-center'  => get_template_directory_uri() . '/assets/images/no-sidebar-center.png'
				),
			)
		)
	);

	/*-------------------------------------------------------------------- 404 Page Settings Section ----------------------------------------------------------------*/
	/**
	 * 404 Page Settings
	 */
	$wp_customize->add_section( 'color_blog_section_pnf_settings',
		array(
			'priority'       => 20,
			'panel'          => 'color_blog_design_panel',
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => __( '404 Page Settings', 'color-blog' )
		)
	);

	/**
	 * Toggle field for Enable/Disable latest posts section at 404 page
	 */
	$wp_customize->add_setting( 'color_blog_enable_pnf_latest_posts',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_pnf_latest_posts',
			array(
				'label'         => __( 'Enable Latest Posts', 'color-blog' ),
				'section'       => 'color_blog_section_pnf_settings',
				'settings'      => 'color_blog_enable_pnf_latest_posts',
				'priority'      => 40,
			)
		)
	);

	/**
	 * Text field for latest posts section title
	 */
	$wp_customize->add_setting( 'color_blog_pnf_latest_title',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => esc_html__( 'You May Like', 'color-blog' ),
			'sanitize_callback' => 'sanitize_text_field'
		)
	);

	$wp_customize->add_control( 'color_blog_pnf_latest_title', array(
		'type'			=> 'text',
		'label'    => esc_html__( 'Section Title', 'color-blog' ),
		'section'       => 'color_blog_section_pnf_settings',
		'priority'      => 45,
		'active_callback' => 'color_blog_enable_pnf_latest_posts_active_callback',
	) );

	/**
	 * Text field for latest posts count
	 */
	$wp_customize->add_setting( 'color_blog_pnf_latest_post_count', array(
		'capability' 	=> 'edit_theme_options',
		'default' 		=> 3,
		'sanitize_callback' => 'absint',
	  ) );
	  
	$wp_customize->add_control( 'color_blog_pnf_latest_post_count', array(
		'type'     => 'number',
		'label'    => esc_html__( 'Post count', 'color-blog' ),
		'section'  => 'color_blog_section_pnf_settings',
		'priority' => 50,
		'active_callback' => 'color_blog_enable_pnf_latest_posts_active_callback',
	) );
}