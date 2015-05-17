<?php

class WpseaPopularPosts extends WP_Widget implements DashboardWidget
{
	protected $min = 1;
	protected $max = 10;
	protected $title = '';

	public function __construct() {
		$this->title = __('WPSEA Popular Posts', 'wpsea_func');
		$widget_options = array(
			'classname' => 'wpsea-popular'
			,'description' => __('Show the most popular posts', 'wpsea_func')
		);

		$control_options = array(
			'width' => '400px'
		);
		parent::__construct(
			'wpsea-popular', // Base ID
			$this->title, // Name
			$widget_options, 
			$control_options
		);
	}

	/**
	 * Describe your function
	 *
	 * @param int $limit_count The number of items. Default is 10. 
	 * @return array
	 */
	protected function popular_posts_sql( $limit_count = 10 ){
		global $wpdb;

		$limit_count = intval( $limit_count );

		$popular = $wpdb->get_results(
			'SELECT id, post_title, comment_count '
			.' FROM ' . $wpdb->prefix . 'posts'
			." WHERE post_type='post' ORDER BY comment_count DESC LIMIT " . $limit_count
		);

		return $popular;
	}


	/**
	 * display a widget of the most popular posts
	 *
	 * @since 0.1
	 *
	 * @param  array $args
	 * @return void
	 */
	public function widget( $args, $instance ) {

		if ( isset($instance['popular_max']) ) {
			$qty = intval( $instance['popular_max'] );
			$popular = $this->popular_posts_sql($qty);
		} else {
			$popular = $this->popular_posts_sql();
		} 

		echo $args['before_widget'];
		echo $args['before_title'] . $this->title . $args['after_title'];

		echo '<ul>';
		foreach( $popular as $post ) :
			$post_link = get_permalink( $post->id );
			echo ' <li><a href="' . $post_link .'">' . $post->post_title .  '</a></li>';
		endforeach;
		echo '</ul>';
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();

		if ( isset( $new_instance['popular_max'] ) ) {
			$instance['popular_max'] = intval( $new_instance['popular_max'] );

		} else {
			$default = (isset($old_instance['popular_max'])) ? $old_instance['popular_max']: 5;
			$instance['popular_max'] = $default;
		} 

		return $instance;
	}

	public function form( $instance ){
		$popular_max =  ( isset($instance['popular_max'])) ? $instance['popular_max']: 2;

	?>
	<p>
		Specify how many popular posts would you like to display.
	</p>
	<div>
		<label for="<?php echo $this->get_field_id( 'popular-range' ); ?>">Quantity</label> 
		<select 
		id="<?php echo $this->get_field_id( 'popular-range' ); ?>" 
		name="<?php echo $this->get_field_name( 'popular_max' ); ?>" >
		<?php foreach ( range($this->min, $this->max) AS $qty ):  ?>
			<option value="<?php echo $qty ?>" <?php selected($popular_max, $qty); ?>><?php echo $qty; ?></option> 
		<?php endforeach; ?> 
		</select>
	</div>
	<?php
	}

}



