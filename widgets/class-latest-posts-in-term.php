<?php

if ( ! class_exists( 'Latest_Posts_In_Term' ) ) :

/**
 * Posts by category; adds a posts list to the sidebar, isolated to specific categories
 *
 * @author Matthew Boynes
 */
class Latest_Posts_In_Term extends WP_Widget {
	public function __construct() {
		$this->WP_Widget( 'Latest_Posts_In_Term', 'Latest posts in same category', array(
			'classname'   => 'Latest_Posts_In_Term',
			'description' => "Displays a list of recent posts from the current post's category"
		) );
	}


	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'limit' => 10
		) );
		$title = $instance['title'];
		$limit = intval( $instance['limit'] );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'limit' ); ?>">Number of posts to display: <input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" /></label></p>
		<?php

	}


	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['limit'] = $new_instance['limit'];
		return $instance;
	}


	function widget( $args, $instance ) {
		if ( !is_single() )
			return;

		$post_id = get_the_ID();
		if ( !$post_id )
			return;

		$instance = wp_parse_args( $instance, array(
			'title' => '',
			'limit' => 10
		) );

		# Get the category
		$cats = get_the_category( $post_id );
		if ( !$cats )
			return;
		usort( $cats, '_usort_terms_by_ID' ); // order by ID
		if ( !isset( $cats[0], $cats[0]->term_id ) )
			return;

		$category = $cats[0]->term_id;
		$title = apply_filters( 'widget_title', $instance['title'] );
		$limit = intval( $instance['limit'] );

		$category_posts = new WP_Query( array(
			'posts_per_page' => $limit,
			'cat'            => $category,
			'post__not_in'   => array( $post_id )
		) );

		// Display organizers
		if ( $category_posts->have_posts() ) :
			echo $before_widget, $before_title, $title, $after_title, '<ul class="latest-posts-widget">';
			while ( $category_posts->have_posts() ) : $category_posts->the_post(); ?>
				<li class="latest-post">
					<h4 class="latest-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
					<div class="latest-post-content"><?php the_excerpt(); ?></div>
				</li>
			<?php endwhile;
			echo '</ul>', $after_widget;
		endif;
		wp_reset_postdata();
	}
}

add_action( 'widgets_init', function() {
     register_widget( 'Latest_Posts_In_Term' );
} );

endif;