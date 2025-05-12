<?php
/**
 * Color Blog manage the Customizer options of header panel.
 *
 * @package Mystery Themes
 * @subpackage Color Blog
 * @since 1.0.0
 */

add_action( 'customize_register', 'color_blog_customize_header_panels_sections_register' );
/**
 * Add panels in the theme cutomizer
 * 
 */
function color_blog_customize_header_panels_sections_register( $wp_customize ){

	/*------------------------------------------ Top Header Section ----------------------------------------*/
	/**
	 * Top Header Section
	 */
	$wp_customize->add_section( 'color_blog_section_top_header',
		array(
			'priority'       => 10,
			'panel'          => 'color_blog_header_panel',
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => __( 'Top Header Settings', 'color-blog' )
		)
	);

	/**
	 * Toggle field for Enable/Disable trending section
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_enable_top_header',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_top_header',
			array(
				'label'         => __( 'Enable Top Header', 'color-blog' ),
				'description' => esc_html__( 'Show/Hide top header section.', 'color-blog' ),
				'section'       => 'color_blog_section_top_header',
				'settings'      => 'color_blog_enable_top_header',
				'priority'      => 10,
			)
		)
	);

	/**
	 * Toggle field for Enable/Disable trending section.
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_enable_trending',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => false,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_trending',
			array(
				'label'    => esc_html__( 'Enable Trending Section', 'color-blog' ),
				'description' => esc_html__( 'Trending section shows the popular tags.', 'color-blog' ),
				'section'       => 'color_blog_section_top_header',
				'settings'      => 'color_blog_enable_trending',
				'priority'      => 10,
				'active_callback' => 'color_blog_enable_top_header_active_callback',
			)
		)
	);

	/**
	 * checkox for before icon in tags.
	 *
	 */
	$wp_customize->add_setting( 'color_blog_enable_trending_tag_before_icon',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( 'color_blog_enable_trending_tag_before_icon', array(
		'type'			=> 'checkbox',
		'label'       => esc_html__( 'Add Icon Before Tag', 'color-blog' ),
		'description' => esc_html__( 'Show/Hide Hash Icon before tag.', 'color-blog' ),
		'section'       => 'color_blog_section_top_header',
		'priority'      => 20,
		'active_callback' => 'color_blog_enable_top_header_trending_active_callback',
	) );
	
	/**
	 * Text field for trending label.
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_trending_label',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => esc_html__( 'Trending Now', 'color-blog' ),
			'sanitize_callback' => 'sanitize_text_field'
		)
	);

	$wp_customize->add_control( 'color_blog_trending_label', array(
		'type'			=> 'text',
		'label'    => esc_html__( 'Trending Label', 'color-blog' ),
		'section'       => 'color_blog_section_top_header',
		'priority'      => 25,
		'active_callback' => 'color_blog_enable_top_header_trending_active_callback',
	) );

	/**
	 * Select field of trending tags orderby.
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_trending_tags_orderby', array(
		'capability' 	=> 'edit_theme_options',
		'default' 		=> '',
		'sanitize_callback' => 'color_blog_sanitize_select',
	  ) );
	  
	$wp_customize->add_control( 'color_blog_trending_tags_orderby', array(
		'type'     => 'select',
		'label'    => esc_html__( 'Tags Orderby', 'color-blog' ),
		'section'  => 'color_blog_section_top_header',
		'default'  => '',
		'priority' => 30,
		'choices'  => array(
			''	  => esc_html__( 'Default', 'color-blog' ),
			'count' => esc_html__( 'Count', 'color-blog' ),
		),
		'active_callback' => 'color_blog_enable_top_header_trending_active_callback',
	) );

	/**
	 * Number field of trending tags count.
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_trending_tags_count', array(
		'capability' 	=> 'edit_theme_options',
		'default' 		=> '5',
		'sanitize_callback' => 'absint',
	  ) );
	  
	$wp_customize->add_control( 'color_blog_trending_tags_count', array(
		'type'     => 'number',
		'label'    => esc_html__( 'Tags Count', 'color-blog' ),
		'section'  => 'color_blog_section_top_header',
		'priority' => 35,
		'active_callback' => 'color_blog_enable_top_header_trending_active_callback',
	) );

	/*------------------------------------------ Header: Extra Options ----------------------------------------*/
	/**
	 * Header Extra Options
	 */
	$wp_customize->add_section( 'color_blog_section_header_extra',
		array(
			'priority'       => 30,
			'panel'          => 'color_blog_header_panel',
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => __( 'Extra Options', 'color-blog' )
		)
	);

	/**
	 * Toggle field for Enable/Disable sticky menu.
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_enable_sticky_menu',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_sticky_menu',
			array(
				'label'    => esc_html__( 'Enable Sticky Menu', 'color-blog' ),
				'section'       => 'color_blog_section_header_extra',
				'settings'      => 'color_blog_enable_sticky_menu',
				'priority'      => 5,
			)
		)
	);

	/**
	 * Toggle field for Enable/Disable social icons.
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_enable_header_social_icons',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => false,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_header_social_icons',
			array(
				'label'    => esc_html__( 'Enable Social Icons', 'color-blog' ),
				'section'       => 'color_blog_section_header_extra',
				'settings'      => 'color_blog_enable_header_social_icons',
				'priority'      => 10,
			)
		)
	);

	/**
	 * Toggle field for Enable/Disable search icon. 
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_enable_search_icon',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_search_icon',
			array(
				'label'    => esc_html__( 'Enable Search Icon', 'color-blog' ),
				'section'       => 'color_blog_section_header_extra',
				'settings'      => 'color_blog_enable_search_icon',
				'priority'      => 15,
			)
		)
	);
}