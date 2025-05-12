<?php
/**
 * Template part for displaying single post
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Mystery Themes
 * @subpackage Color Blog
 * @since 1.0.0
 */

if( has_post_thumbnail() ) {
    $post_class = 'has-thumbnail';
} else {
    $post_class = 'no-thumbnail';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>
	<div class="post-thumbnail">
		<?php if( has_post_thumbnail() ){ ?>
				<img src="<?php echo esc_url( get_the_post_thumbnail_url() ); ?>" alt="thumbnail">
		<?php } ?>
		<div class="post-info-wrap">
			<div class="post-cat"><?php color_blog_article_categories_list(); ?></div>
			<div class="entry-meta"> 
				<?php 
					color_blog_posted_on();
					color_blog_posted_by();
				?> 
			</div>
			<?php the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
	    </div><!--.post-info-wrap -->
	</div><!-- .post-thumbnail -->

	<div class="entry-content">
		<?php the_content(); ?>
	</div> <!-- .entry-content -->

	<footer class="entry-footer">
		<?php color_blog_entry_footer(); ?>
	</footer><!-- .entry-footer -->
	<?php get_template_part( 'template-parts/author/author', 'box' ); ?>
</article><!-- #post-<?php the_ID(); ?> -->