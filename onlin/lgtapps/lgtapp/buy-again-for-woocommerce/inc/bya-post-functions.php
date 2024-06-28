<?php
/**
 * Post functions
 *
 * @package Buy Again\Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'bya_create_new_buy_again_log' ) ) {

	/**
	 * Create New Buy Again Log
	 *
	 * @since 1.0
	 * @param Array $meta_args Meta data arguments.
	 * @param Array $post_args Post data arguments.
	 * @return Integer/String
	 */
	function bya_create_new_buy_again_log( $meta_args, $post_args = array() ) {

		$object = new BYA_Buy_Again_Log();
		$id     = $object->create( $meta_args, $post_args );

		return $id;
	}
}

if ( ! function_exists( 'bya_get_buy_again_log' ) ) {

	/**
	 * Get Buy Again Log Object
	 *
	 * @since 1.0
	 * @param Integer $id Buy Again Log ID.
	 * @return Object
	 */
	function bya_get_buy_again_log( $id ) {

		$object = new BYA_Buy_Again_Log( $id );

		return $object;
	}
}

if ( ! function_exists( 'bya_update_buy_again_log' ) ) {

	/**
	 * Update Buy Again Log
	 *
	 * @since 1.0
	 * @param Integer $id Buy Again Log ID.
	 * @param Array   $meta_args Meta data arguments.
	 * @param Array   $post_args Post data arguments.
	 * @return Object
	 */
	function bya_update_buy_again_log( $id, $meta_args, $post_args = array() ) {
		$object = new BYA_Buy_Again_Log( $id );
		$object->update( $meta_args, $post_args );

		return $object;
	}
}

if ( ! function_exists( 'bya_delete_buy_again_log' ) ) {
	/**
	 * Delete Buy Again Log
	 *
	 * @since 1.0
	 * @param integer $id  post id.
	 * @param boolean $force Force.
	 * @return boolean
	 */
	function bya_delete_buy_again_log( $id, $force = true ) {
		wp_delete_post( $id, $force );
		return true;
	}
}

if ( ! function_exists( 'bya_is_user_having_buy_again_list' ) ) {

	/**
	 * Get User have a Buy Again List.
	 *
	 * @since 1.0
	 * @param integer $user_id  User ID.
	 * @return ID (or) boolean
	 */
	function bya_is_user_having_buy_again_list( $user_id ) {

		$data = get_posts(
			array(
				'post_type'   => BYA_Register_Post_Types::BUY_AGAIN_LIST_POSTTYPE,
				'post_status' => 'publish',
				'author'      => $user_id,
				'fields'      => -1,
			)
		);

		if ( bya_check_is_array( $data ) ) {
			return $data[0]->ID;
		}

		return false;
	}
}

if ( ! function_exists( 'bya_is_prodcut_already_in_buy_again_list' ) ) {

	/**
	 * Get Buy Again List ID
	 *
	 * @since 1.0
	 * @param integer $user_id  User ID.
	 * @param integer $product_id  Product ID.
	 * @return ID (or) bool
	 */
	function bya_is_prodcut_already_in_buy_again_list( $user_id, $product_id ) {

		$data = get_posts(
			array(
				'post_type'   => BYA_Register_Post_Types::BUY_AGAIN_LIST_POSTTYPE,
				'post_status' => 'bya_product',
				'author'      => $user_id,
				'fields'      => 'ids',
				'meta_query'  => array(
					array(
						'key'     => 'bya_product_id',
						'value'   => $product_id,
						'compare' => '==',
					),
				),
			)
		);

		return ( bya_check_is_array( $data ) ) ? $data[0] : false;
	}
}

if ( ! function_exists( 'bya_bya_product_post_id' ) ) {

	/**
	 * Get Buy Again List ID
	 *
	 * @since 1.0
	 * @param Integer $user_id User ID.
	 * @param Integer $post_parent_id Post Parent ID.
	 * @return array
	 */
	function bya_bya_product_post_id( $user_id, $post_parent_id ) {
		return get_posts(
			array(
				'post_type'      => BYA_Register_Post_Types::BUY_AGAIN_LIST_POSTTYPE,
				'post_status'    => 'bya_products',
				'parent'         => $post_parent_id,
				'author'         => $user_id,
				'fields'         => 'ids',
				'posts_per_page' => '-1',
			)
		);
	}
}
