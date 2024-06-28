<?php

/*
 * Master Log
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Buy_Again_Log' ) ) {

	/**
	 * BYA_Buy_Again_Log Class.
	 */
	class BYA_Buy_Again_Log extends BYA_Post {

		/**
		 * Post Type
		 * 
		 * @param String
		 */
		protected $post_type = BYA_Register_Post_Types::BUY_AGAIN_LIST_POSTTYPE;

		/**
		 * Post Status
		 */
		protected $post_status = 'publish';

		/**
		 * User ID
		 */
		protected $user_id;

		/**
		 * Customer ID
		 */
		protected $bya_customer_id;

		/**
		 * Product ID
		 */
		protected $bya_product_id;

		/**
		 * Order ID
		 */
		protected $bya_order_id;

		/**
		 * Product Quantity
		 */
		protected $bya_product_quantity;

		/**
		 * Product Price
		 */
		protected $bya_product_price;

		/**
		 * Total Order Count
		 */
		protected $bya_total_orders;

		/**
		 * Total Product Count
		 */
		protected $bya_total_products;

		/**
		 * Total Earnings
		 */
		protected $bya_total_earnings;

		/**
		 * Saved Date
		 */
		protected $bya_activity_date;

		/**
		 * Meta data keys
		 */
		protected $meta_data_keys = array(
			'bya_product_id'       => '',
			'bya_customer_id'      => '',
			'bya_order_id'         => array(),
			'bya_product_quantity' => '',
			'bya_product_price'    => '',
			'bya_total_orders'     => 0,
			'bya_total_products'   => 0,
			'bya_total_earnings'   => 0,
			'bya_activity_date'    => 0,
		);

		/**
		 * Prepare extra post data
		 */
		protected function load_extra_postdata() {
			$this->user_id      = $this->post->post_author;
			$this->created_date = $this->post->post_date_gmt;
		}

		/**
		 * Get formatted created datetime
		 */
		public function get_formatted_created_date() {

			return BYA_Date_Time::get_date_object_format_datetime( $this->get_created_date() );
		}

		/**
		 * Setters and Getters
		 */

		/**
		 * Set User ID
		 */
		public function set_user_id( $value ) {
			$this->user_id = $value;
		}

		/**
		 * Set Order ID
		 */
		public function set_order_id( $value ) {
			$this->bya_order_id = $value;
		}

		/**
		 * Set User id
		 */
		public function set_customer_id( $value ) {
			$this->bya_customer_id = $value;
		}

		/**
		 * Set Product id
		 */
		public function set_product_id( $value ) {
			$this->bya_product_id = $value;
		}

		/**
		 * Set Product Quantity
		 */
		public function set_product_quantity( $value ) {

			$this->bya_product_quantity = $value;
		}

		/**
		 * Set Product price
		 */
		public function set_product_price( $value ) {

			$this->bya_product_price = $value;
		}

		/**
		 * Set created date
		 */
		public function set_created_date( $value ) {
			$this->created_date = $value;
		}

		/**
		 * Set total orders
		 */
		public function set_total_orders( $value ) {
			$this->bya_total_orders = $value;
		}

		/**
		 * Set total products
		 */
		public function set_total_products( $value ) {
			$this->bya_total_products = $value;
		}

		/**
		 * Set Total earnings
		 */
		public function set_total_earnings( $value ) {
			$this->bya_total_earnings = $value;
		}

		/**
		 * Set Activity date
		 */
		public function set_activity_date( $value ) {
			$this->bya_activity_date = $value;
		}

		/**
		 * Get User ID
		 */
		public function get_user_id() {

			return $this->user_id;
		}

		/**
		 * Get Product ID
		 */
		public function get_product_id() {

			return $this->bya_product_id;
		}

		/**
		 * Get User Email
		 */
		public function get_customer_id() {

			return $this->bya_customer_id;
		}

		/**
		 * Get Order ID
		 */
		public function get_order_id() {

			return $this->bya_order_id;
		}

		/**
		 * Get Product Quantity
		 */
		public function get_product_quantity() {

			return $this->bya_product_quantity;
		}

		/**
		 * Get Product price
		 */
		public function get_product_price() {

			return $this->bya_product_price;
		}

		/**
		 * Get created date
		 */
		public function get_created_date() {

			return $this->created_date;
		}

		/**
		 * Get total orders count
		 */
		public function get_total_orders() {
			return $this->bya_total_orders;
		}

		/**
		 * Get total products count
		 */
		public function get_total_products() {
			return $this->bya_total_products;
		}

		/**
		 * Get total earnings
		 */
		public function get_total_earnings() {
			return $this->bya_total_earnings;
		}

		/**
		 * Get activity date
		 */
		public function get_activity_date() {
			return $this->bya_activity_date;
		}

	}

}

