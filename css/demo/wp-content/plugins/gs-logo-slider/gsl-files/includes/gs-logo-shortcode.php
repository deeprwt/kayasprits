<?php 

//--------- Getting values from setting panel ---------------- //

function gs_l_get_option( $option, $section, $default = '' ) {

    $options = get_option( $section );
 
    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
 
    return $default;
}

add_action('wp_footer','gs_ls_slider_trigger');

function gs_ls_slider_trigger(){

	$gs_l_ctrl = gs_l_get_option( 'gs_l_ctrl', 'gs_l_control', 'on' );
	$gs_l_ctrl = ($gs_l_ctrl === 'off' ? 0 : 1);
	$gs_l_slide_speed = gs_l_get_option( 'gs_l_slide_speed', 'gs_l_general', '500' );
	$gs_l_inf_loop = gs_l_get_option( 'gs_l_inf_loop', 'gs_l_general', 'on' );
	$gs_l_inf_loop = ($gs_l_inf_loop === 'off' ? 0 : 1);
	$gs_l_move_logo = gs_l_get_option( 'gs_l_move_logo', 'gs_l_styling', '1' );
	$gs_l_max_logo = gs_l_get_option( 'gs_l_max_logo', 'gs_l_styling', '5' );
	$gs_l_min_logo = gs_l_get_option( 'gs_l_min_logo', 'gs_l_styling', '1' );
?>
<script type="text/javascript">
jQuery(document).ready(function(){
  jQuery('.gs_logo_container').bxSlider({
  	slideWidth: 200,
    minSlides: <?php echo $gs_l_min_logo;?>,
    maxSlides: <?php echo $gs_l_max_logo;?>,
    slideMargin: 10,
  	moveSlides:  <?php echo $gs_l_move_logo;?>,
  	speed: <?php echo $gs_l_slide_speed;?>,
  	controls: <?php echo $gs_l_ctrl;?>,
  	autoHover: true,
  	pager: false,
  	auto: <?php echo $gs_l_inf_loop;?>
  });
 
});
</script>
<?php
}


// ---------- Shortcode [gs_logo] -------------

add_shortcode( 'gs_logo', 'gs_logo_shortcode' );

function gs_logo_shortcode( $atts ) {

	extract(shortcode_atts( 
			array(
			'posts' 	=> 20,
			'order'		=> 'DESC',
			'orderby'   => 'date',
			'title'		=> 'no'
			), $atts 
		));

	$loop = new WP_Query(
		array(
			'post_type'	=> 'gs-logo-slider',
			'order'		=> $order,
			'orderby'	=> $orderby,
			'posts_per_page'	=> $posts
			)
		);

	$output = '<div class="gs_logo_container">';
		if ( $loop->have_posts() ) {
			
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$meta = get_post_meta( get_the_id() );
				
				$gs_logo_id = get_post_thumbnail_id();
				$gs_logo_url = wp_get_attachment_image_src($gs_logo_id, array(200,200), true);
				$gs_logo = $gs_logo_url[0];
				$gs_logo_alt = get_post_meta($gs_logo_id,'_wp_attachment_image_alt',true);

				$output .= '<div class="gs_logo_single">';

					if ($meta['client_url'][0]) :
				 		$output .= '<a href="'. $meta['client_url'][0] .'" target="_blank">';
				 	endif;

				 	if ($gs_logo) :
						$output .= '<img src="'.$gs_logo.'" alt="'.$gs_logo_alt.'" >';
					endif;

					if ($meta['client_url'][0]) :
						$output .= '</a>';
					endif;
					
					if ( $title == "yes" ) :
						$output .= '<h3 class="gs_logo_title">'. get_the_title() .'</h3>';
					endif;
				$output .= '</div>';
			}

		} else {
			$output .= "No Logo Added!";
		}

		wp_reset_postdata();

	$output .= '</div>';

	return $output;
}