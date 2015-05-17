<?php

class WpseaLatestPosts extends WP_Widget implements DashboardWidget
{
	protected $min = 1;
	protected $max = 10;
	protected $title = '';

	public function __construct() {
		$this->title = __( 'WPSEA Latest Posts', 'wpsea_func' );

		$widget_options = array(
			'classname' => 'wpsea-latest'
			,'description' => __('Show the most recent posts', 'wpsea_func')
		);

		$control_options = array(
			'width' => '400px'
		);
		parent::__construct(
			'wpsea-latest', // Base ID
			$this->title, // Name
			$widget_options, 
			$control_options
		);
	}

	/**
	 * display a widget of the most recent posts
	 *
	 * @since 0.1
	 *
	 * @param  array $args
	 * @return void
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];
		echo $args['before_title'] . $this->title . $args['after_title'];

		$query_args = array( 'posts_per_page' => 1, 'post_status' => 'publish' );
		$latest = new WP_Query( $query_args );

		if ( $latest->have_posts() ) :
			echo '<ul>';
			while ( $latest->have_posts() ) : $latest->the_post();
				$post_link = get_the_permalink();
				?><li><a href="<?php echo $post_link; ?>"><?php the_title(); ?></a></li>
			<?php
			endwhile;
			echo '</ul>';
			wp_reset_postdata();

		else : ?>
			<p>stay tuned for the next post</p>
		<?php
		endif;

		echo $args['after_widget'];
	}

	
	public function update( $new_instance, $old_instance ){
		$instance = array();
		
		if ( isset( $new_instance['latest_max'] ) ) {
			$instance['latest_max'] = intval( $new_instance['latest_max'] );

		} else {
			$default = (isset($old_instance['latest_max'])) ? $old_instance['latest_max']: 5;
			$instance['latest_max'] = $default;
		} 

		return $instance;
	}

	public function form( $instance ){
		$latest_max = ( isset( $instance['latest_max'] ) ) ? $instance['latest_max'] : 3;
	?>
	<p>
		Specify how many of the latest posts would you like to display.
	</p>
	<div>
		<label for="<?php echo $this->get_field_id( 'latest-max' ); ?>">Quantity</label> 
		<select 
		id="<?php echo $this->get_field_id( 'latest-max' ); ?>" 
		name="<?php echo $this->get_field_name( 'latest_max' ); ?>" >
		<?php foreach ( range($this->min, $this->max) AS $qty ):  ?>
			<option value="<?php echo $qty ?>" <?php selected($latest_max, $qty); ?>><?php echo $qty; ?></option> 
		<?php endforeach; ?> 
		</select>
	</div>
	<?php
	}

}



