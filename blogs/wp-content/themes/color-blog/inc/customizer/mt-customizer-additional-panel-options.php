<?php
/**
 * Color Blog manage the Customizer options of additional panel.
 *
 * @package Mystery Themes
 * @subpackage Color Blog
 * @since 1.0.0
 */
add_action( 'customize_register', 'color_blog_customize_additinal_panels_sections_register' );
/**
 * Add Additional panels in the theme cutomize
 * 
 */
function color_blog_customize_additinal_panels_sections_register( $wp_customize ){

/*------------------------------------------------ Social Icons Section ------------------------------------------------*/
	/**
	 * Social Icons
	 */
	$wp_customize->add_section( 'color_blog_section_social_icons',
		array(
			'title'    => esc_html__( 'Social Icons', 'color-blog' ),
			'panel'          => 'color_blog_additional_panel',
			'capability'     => 'edit_theme_options',
			'priority'       => 5,
			'theme_supports' => '',
		)
	);

	/**
	 * Repeater field for social icons
	 * 
	 */
	$wp_customize->add_setting(
		'color_blog_social_icons', 
		array(
			'capability'       => 'edit_theme_options',
			'default'          => json_encode( array(
					array(
						'social_icon' => 'fa fa-twitter',
						'social_url'  => '#',
					),
					array(
						'social_icon' => 'fa fa-pinterest',
						'social_url'  => '#',
					)
				)
			),
			'sanitize_callback' => 'wp_kses_post'
		)
	);
	$wp_customize->add_control( new Color_Blog_Control_Repeater(
		$wp_customize, 
			'color_blog_social_icons',
			array(
				'label'           => __( 'Social Media', 'color-blog' ),
				'section'         => 'color_blog_section_social_icons',
				'settings'        => 'color_blog_social_icons',
				'priority'        => 5,
				'color_blog_box_label_text'       => __( 'Social Media Icons','color-blog' ),
				'color_blog_box_add_control_text' => __( 'Add Icon','color-blog' )
			),
			array(
				'social_icon' => array(
					'type'	  => 'social_icon',	
					'label'   => esc_html__( 'Social Icon', 'color-blog' ),
					'description' => __( 'Choose social media icon.', 'color-blog' )
				),
				'social_url'  => array(
					'type'    => 'url',
					'label'   => esc_html__( 'Social Link URL', 'color-blog' ),
					'description' => __( 'Enter social media url.', 'color-blog' )
				),
			)
		) 
	);

	/*------------------------------------------------ Breadcrumbs Section ------------------------------------------------*
	/**
	 * Breadcrumbs
	 */
	$wp_customize->add_section( 'color_blog_section_breadcrumbs',
		array(
			'title'    => esc_html__( 'Breadcrumbs', 'color-blog' ),
			'panel'          => 'color_blog_additional_panel',
			'capability'     => 'edit_theme_options',
			'priority'       => 10,
			'theme_supports' => '',
		)
	);

	/** 
	 * Toggle field for Enable/Disable breadcrumbs.
	 * 
	 */
	$wp_customize->add_setting( 'color_blog_enable_breadcrumb_option',
		array(
			'capability'        => 'edit_theme_options',
			'default'           => true,
			'sanitize_callback' => 'color_blog_sanitize_checkbox'
		)
	);

	$wp_customize->add_control( new Color_Blog_Control_Toggle(
		$wp_customize, 'color_blog_enable_breadcrumb_option',
			array(
				'label'         => __( 'Enable Breadcrumbs', 'color-blog' ),
				'section'       => 'color_blog_section_breadcrumbs',
				'settings'      => 'color_blog_enable_breadcrumb_option',
				'priority'      => 5,
			)
		)
	);

	/*------------------------------------------------ Categories Color Section ------------------------------------------------*/
	/**
	 * Categories Color
	 */
	$wp_customize->add_section( 'color_blog_categories_color_section',
		array(
			'title'    => esc_html__( 'Categories Color', 'color-blog' ),
			'panel'          => 'color_blog_additional_panel',
			'capability'     => 'edit_theme_options',
			'priority'       => 15,
			'theme_supports' => '',
		)
	);

	/**
	 * Setting for categories color 
	 *  
	 */
	$priority = 5;
	$categories = get_categories( array( 'hide_empty' => 1 ) );

	foreach ( $categories as $category_list ) {
		$wp_customize->add_setting( 'color_blog_category_color_'.esc_attr( $category_list->slug ),
			array(
				'capability'        => 'edit_theme_options',
				'default'           => '#3b2d1b',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize, 'color_blog_category_color_'.esc_attr( $category_list->slug ),
				array(
					'label'      => esc_attr( $category_list->name ).' Color',
					'section'    => 'color_blog_categories_color_section',
					'settings'   => 'color_blog_category_color_'.esc_attr( $category_list->slug ),
					'priority'   => absint( $priority )
				)
			)
		);
	$priority += 5;
	}
}