<?php
/**
 * Gutenberg block for rijksvideo
 * Description: Gutenberg block to embed a rijksvideo video player via ACF and shortcode.
 * Version: 1.1.3
 * Version description: Removed stale, unsafe jQuery file.
 * Author: Paul van Buuren
 * Text Domain: rijksvideo-translate
 */

defined( 'ABSPATH' ) || exit;

// ----------------------------------------------
// 1. Register ACF Block
// ----------------------------------------------
add_action( 'acf/init', 'rijksvideo_register_acf_block' );
function rijksvideo_register_acf_block() {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	acf_register_block_type( [
		'name'            => 'rijksvideo',
		'title'           => __( 'RHS video block', "rijksvideo-translate" ),
		'description'     => __( 'Embed a rijksvideo video player.', "rijksvideo-translate" ),
		'category'        => 'media',
		'icon'            => 'video-alt3',
		'keywords'        => [ 'rijksvideo', 'video', 'player', 'rhs' ],
		'render_callback' => 'rijksvideo_render_block',
		'enqueue_style'   => plugin_dir_url( __FILE__ ) . 'assets/css/block-editor-rijksvideo.css',
		'supports'        => [
			'align'  => false,
			'anchor' => true,
		],
	] );
}

// ----------------------------------------------
// 2. Register ACF Field Group for the block
// ----------------------------------------------
add_action( 'acf/init', 'rijksvideo_register_acf_fields' );
function rijksvideo_register_acf_fields() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}


	acf_add_local_field_group( array(
		'key'                   => 'group_69e8ae4bb1049',
		'title'                 => 'rijksvideo Block Fields',
		'fields'                => array(
			array(
				'key'           => 'field_69e8ae4c3479d',
				'label'         => __( 'Select a video', "rijksvideo-translate" ),
				'name'          => 'rijksvideo_post',
				'type'          => 'post_object',
				'instructions'  => __( 'Only published videos can be embedded.', "rijksvideo-translate" ),
				'required'      => 1,
				'post_type'     => [ RHSWP_CPT_RIJKSVIDEO ],
				'post_status'   => [ 'publish' ],
				'return_format' => 'id',
				'ui'            => 1,
				'allow_null'    => 0,
				'multiple'      => 0,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'acf/rijksvideo',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => '',
		'active'                => true,
		'description'           => '',
		'show_in_rest'          => 0,
		'display_title'         => '',
		'allow_ai_access'       => false,
		'ai_description'        => '',
	) );


}

// ----------------------------------------------
// 3. Block render callback
// ----------------------------------------------
function rijksvideo_render_block( $block, $content = '', $is_preview = false ) {
	$post_id = (int) get_field( 'rijksvideo_post' );

	if ( ! $post_id ) {
		if ( $is_preview ) {
			echo '<p class="rijksvideo-block-preview">' .
			     esc_html__( 'Please select a rijksvideo.', "rijksvideo-translate" ) .
			     '</p>';
		}

		return;
	}


	// In preview mode only show an image of the video
	if ( $is_preview ) {
		$shortcode = '[' . RHSWP_CPT_RIJKSVIDEO . ' id="' . $post_id . '" preview="true"]';
		echo '<div class="rijksvideo-block-preview">';
		echo do_shortcode( $shortcode );
		echo '</div>';

		return;
	} else {
		// Generate the shortcode and output it
		$shortcode = '[' . RHSWP_CPT_RIJKSVIDEO . ' id="' . $post_id . '"]';
		// Front-end: execute the shortcode
		echo do_shortcode( $shortcode );

	}

}

