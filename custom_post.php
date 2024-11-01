<?php
/**
* Plugin Name: Your Custom Post Type
* Plugin URI: #
* Description: A Business Review Plugin
* Version: 1.0 
* Author: Ministry Of Cleaning Melbourne 
* Author URI: https://ministryofcleaning.com.au/
* License: GPL12
*/


// Register Custom Post Type
function YCPT_customposttype() {

	$labels = array(
		'name'                  => _x( 'Business Reviews', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Business Review', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Business Review', 'text_domain' ),
		'name_admin_bar'        => __( 'Business Review', 'text_domain' ),
		'archives'              => __( 'Review Archives', 'text_domain' ),
		'attributes'            => __( 'Review Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Review', 'text_domain' ),
		'all_items'             => __( 'All Reviews', 'text_domain' ),
		'add_new_item'          => __( 'Add New Review', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Review', 'text_domain' ),
		'edit_item'             => __( 'Edit Review', 'text_domain' ),
		'update_item'           => __( 'Update Review', 'text_domain' ),
		'view_item'             => __( 'View Review', 'text_domain' ),
		'view_items'            => __( 'View Reviews', 'text_domain' ),
		'search_items'          => __( 'Search Review', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into Review', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Review list', 'text_domain' ),
		'items_list_navigation' => __( 'Reviews list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter reviews list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Business Review', 'text_domain' ),
		'description'           => __( 'Post Type Description', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields', 'post-formats' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 20,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'business_review', $args );

}
add_action( 'init', 'YCPT_customposttype', 0 );

/**
 * Build custom field meta box
 *
 * @param post $post The post object
 */
function business_review_build_meta_box( $post ){
	wp_nonce_field( basename( __FILE__ ), 'business_review_meta_box_nonce' );
	$current_url = get_post_meta( $post->ID, '_business_url', true );
	$reviewer_name = get_post_meta( $post->ID, '_reviewer_name', true );
	$stars = array( '1', '2', '3', '4', '5');
	
	$current_star = ( get_post_meta( $post->ID, '_business_rating', true ) ) ? get_post_meta( $post->ID, '_business_rating', true ) : array();
	?>
	<div class='inside'>

		<h3><?php _e( 'Business Url', 'business_review_plugin' ); ?></h3>
		<p>
			<input type="text" name="business_url" value="<?php echo $current_url; ?>" /> 
		</p>

		<h3><?php _e( 'Reviewer Name', 'business_review_plugin' ); ?></h3>
		<p>
			<input type="text" name="reviewer_name" value="<?php echo $reviewer_name; ?>" /> 
		</p>

		<h3><?php _e( 'Ratings', 'business_review_plugin' ); ?></h3>
		<p>
		<?php
	$i=1;
			foreach ( $stars as $star ) {
				?>
				<input type="radio" name="stars" value="<?php echo $star; ?>" <?php checked( ( in_array( $star, $current_star ) ) ? $star : '', $star ); ?> /><?php echo $star; ?> <br />
				<?php
				$i++;
			}
		?>
		</p>
	</div>
	<?php
}


/**
 * Store custom field meta box data
 *
 * @param int $post_id The post ID.
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
 */
function business_review_save_meta_box_data( $post_id ){
	// verify taxonomies meta box nonce
	if ( !isset( $_POST['business_review_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['business_review_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}
	// store custom fields values
	// cholesterol string
	if ( isset( $_REQUEST['business_url'] ) ) {
		update_post_meta( $post_id, '_business_url', sanitize_text_field( $_POST['business_url'] ) );
	}
	
	// store custom fields values
	// carbohydrates string
	if ( isset( $_REQUEST['reviewer_name'] ) ) {
		update_post_meta( $post_id, '_reviewer_name', sanitize_text_field( $_POST['reviewer_name'] ) );
	}
	
	// store custom fields values
	if( isset( $_POST['stars'] ) ){
		$stars = (array) $_POST['stars'];
		// sinitize array
		$stars = array_map( 'sanitize_text_field', $stars );
		// save data
		update_post_meta( $post_id, '_business_rating', $stars );
	}else{
		// delete data
		delete_post_meta( $post_id, '_business_rating' );
	}
}
add_action( 'save_post_business_review', 'business_review_save_meta_box_data' );


function business_review_add_meta_boxes( $post ){
	add_meta_box( 'business_review_meta_box', __( 'Business Review Detail', 'business_review_example_plugin' ), 'business_review_build_meta_box', 'business_review', 'side', 'high' );
}
add_action( 'add_meta_boxes_business_review', 'business_review_add_meta_boxes' );
?>