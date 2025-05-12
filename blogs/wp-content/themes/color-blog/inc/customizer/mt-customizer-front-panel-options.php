<?php
/**
 * Customizer fields for  front slider section
 * 
 */

add_action( 'customize_register', 'color_blog_customize_slider_panels_sections_register' );
/**
 * Add panels in the theme cutomizer
 * 
 */
function color_blog_customize_slider_panels_sections_register( $wp_customize ){

	/*--------------------------------------------------------- Slider Section ------------------------------------------------ */
	/**
	 * Slider Settings
	 */
	$wp_customize->add_section( 'color_blog_section_slider',
		array(
			'priority'       => 10,
			'panel'          => 'color_blog_front_section_panel',
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => __( 'Slider Settings', 'color-blog' )
		)
	);

	/**
	 * Toggle field for slider option
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_section_slider_option',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => false,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_section_slider_option',
			array(
				'label'         => __( 'Enable Slider Section', 'color-blog' ),
				'section'       => 'color_blog_section_slider',
				'settings'      => 'color_blog_section_slider_option',
				'priority'      => 5,
			)
		)
	);

	/**
	 * Select field for slider cat select
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_section_slider_cat', array(
		'capability' 	=> 'edit_theme_options',
		'default' 		=> '',
		'sanitize_callback' => 'color_blog_sanitize_select',
	  ) );
	  
	$wp_customize->add_control( 'color_blog_section_slider_cat', array(
		'type'     => 'select',
		'label'    => esc_html__( 'Slider category', 'color-blog' ),
		'description' => esc_html__( 'Choose default post category', 'color-blog' ),
		'section'  => 'color_blog_section_slider',
		'default'  => '',
		'priority' => 30,
		'choices'  => color_blog_select_categories_list(),
		'active_callback' => 'color_blog_section_slider_option_active_callback',
	) );

	/*--------------------------------------------------------- Featured Posts Section ------------------------------------------------ */
	/**
	 * Featured Slider Settings
	 */
	$wp_customize->add_section( 'color_blog_section_top_featured_post',
		array(
			'priority'       => 20,
			'panel'          => 'color_blog_front_section_panel',
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'    => esc_html__( 'Featured Posts Settings', 'color-blog' ),
		)
	);

	/**
	 * Toggle field for featured slider option
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_section_top_featured_posts_option',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_section_top_featured_posts_option',
			array(
				'label'    => esc_html__( 'Enable Featured Posts Section', 'color-blog' ),
				'description' => 'This section is displayed after the slider content at the right side minimizing the slider width.',
				'section'       => 'color_blog_section_top_featured_post',
				'settings'      => 'color_blog_section_top_featured_posts_option',
				'priority'      => 5,
			)
		)
	);

	/**
	 * Text field for Featured Posts Title 
	 */
	$wp_customize->add_setting( 'color_blog_top_featured_posts_title',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => esc_html__( 'Featured News', 'color-blog' ),
			'sanitize_callback' => 'sanitize_text_field'
		)
	);

	$wp_customize->add_control( 'color_blog_top_featured_posts_title', array(
		'type'			=> 'text',
		'label'    => esc_html__( 'Featured News', 'color-blog' ),
		'section'       => 'color_blog_section_top_featured_post',
		'priority'      => 10,
		'active_callback' => 'color_blog_section_top_featured_posts_option_active_callback',
	) );	

	/**
	 * Select field for featured posts type.
	 */
	$wp_customize->add_setting( 'color_blog_top_featured_post_order', array(
		'capability' 	=> 'edit_theme_options',
		'default' 		=> 'default',
		'sanitize_callback' => 'color_blog_sanitize_select',
	  ) );
	  
	$wp_customize->add_control( 'color_blog_top_featured_post_order', array(
		'type'     => 'select',
		'label'    => esc_html__( 'Featured Post Order', 'color-blog' ),
		'section'  => 'color_blog_section_top_featured_post',
		'priority' => 15,
		'choices'  => array(
			'default'   => __( 'Latest Posts', 'color-blog' ),
            'random'    => __( 'Random Posts', 'color-blog' ),
		),
		'active_callback' => 'color_blog_section_top_featured_posts_option_active_callback',
	) );
}