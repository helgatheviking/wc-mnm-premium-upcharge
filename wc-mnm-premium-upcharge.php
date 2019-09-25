<?php
/*
 * Plugin Name: WooCommerce Mix and Match: Premium Upcharge
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 * Description: Increase container cost when certain products are selected
 * Version: 1.0.0.alpha.1
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com/
 *
 * Text Domain: wc-mnm-premium-upcharge
 * Domain Path: /languages/
 *
 * Requires at least: 5.2
 * Tested up to: 5.2
 *
 * WC requires at least: 3.4
 * WC tested up to: 3.4.5
 *
 * Copyright: © 2019 Kathy Darling
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_MNM_Premium_Upcharge {


	/**
	 * Upcharge taxonomy.
	 *
	 * @var string
	 */
	public static $taxonomy = 'product_tag';

	/**
	 * Upcharges by Taxonomy.
	 *
	 * @var string
	 */
	public static $upcharges = array( 
		'premium' => '10.00',
		'ultra'   => '50.00'
	);

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public static $version = '1.0.0.alpha.1';

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url = '';

	/**
	 * Plugin Path.
	 *
	 * @var string
	 */
	public $plugin_path = '';

	/**
	 * Plugin URL.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		if( $this->plugin_url ) {
			$this->plugin_url = plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename(__FILE__) );
		}
		return $this->plugin_url;
	}

	/**
	 * Plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		if( $this->plugin_path ) {
			$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		return $this->plugin_path;
	}

	/**
	 * Fire in the hole!
	 */
	public static function init() {
		
		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );


		/*
		 * Product.
		 */
		if( ! is_admin() ) {
			add_filter( 'woocommerce_mnm_get_children', array( __CLASS__, 'add_upcharges' ), 10, 2 );
			add_filter( 'woocommerce_mnm_priced_per_product', array( __CLASS__, 'priced_per_product' ), 10, 2 );
			add_filter( 'woocommerce_mnm_has_discount', array( __CLASS__, 'ignore_discount' ), 10, 2 );
			add_action( 'woocommerce_mnm_child_item_details', array( __CLASS__, 'remove_price_template' ), 1, 2 );	
			add_action( 'woocommerce_mnm_child_item_details', array( __CLASS__, 'restore_price_template' ), 999, 2 );	

			
		}


		//add_action( 'woocommerce_mix-and-match_add_to_cart', array( __CLASS__, 'front_end_setup') , 1 );
		

	}


	/*-----------------------------------------------------------------------------------*/
	/* Localization */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Make the plugin translation ready
	 *
	 * @return void
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-mnm-premium-upcharge' , false , dirname( plugin_basename( __FILE__ ) ) .  '/languages/' );
	}


	/*-----------------------------------------------------------------------------------*/
	/* Front End Display */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Add upcharges.
	 *
	 * @param  array $children an array of product objects
	 * @param  obj WC_Product_Mix_and_Match
	 * @return array 
	 */
	public static function add_upcharges( $children, $product ) {

		foreach( $children as $id => $child ) {

			$terms = get_the_terms( $child->get_id(), self::$taxonomy );

			$terms = $terms !== false && ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'slug' ) : array();	

			$intersection = array_intersect( $terms, array_keys( self::$upcharges ) );

			if( ! empty( $intersection ) ) {
				// Flag on parent.
				$product->add_meta_data( '_has_premium_contents', 'yes', true );

				// Modify price of children.
				foreach ( $intersection as $term ) {
					$child->set_price( self::$upcharges[$term] );
					$child->set_regular_price( self::$upcharges[$term] );
					$child->set_sale_price( self::$upcharges[$term] );
				}
			} else {
				$child->set_price(0);
				$child->set_regular_price(0);
				$child->set_sale_price(0);
			}
			$children[$id] = $child;

		}

		return $children;

	}


	/**
	 * Simulate priced per product.
	 *
	 * @param  bool
	 * @param  obj WC_Product_Mix_and_Match
	 * @return bool 
	 */
	public static function priced_per_product( $priced_per_product, $product ) {
		if( $product->get_meta( '_has_premium_contents' ) == 'yes' ) {
			$priced_per_product = true;			
		}
		return $priced_per_product;
	}


	/**
	 * Ignore discount.
	 *
	 * @param  bool
	 * @param  obj WC_Product_Mix_and_Match
	 * @return bool 
	 */
	public static function ignore_discount( $has_discount, $product ) {
		if( $product->get_meta( '_has_premium_contents' ) == 'yes' ) {
			$has_discount = false;			
		}
		return $has_discount;
	}


	/**
	 * Remove price for $0.
	 *
	 * @param obj $mnm_item WC_Product of child item
	 * @param obj WC_Mix_and_Match $product the parent container
	 */
	public static function remove_price_template( $mnm_item, $parent_product ) {
		if( $parent_product->get_meta( '_has_premium_contents' ) == 'yes' ) {
			if( $mnm_item->get_price() == 0 ) {
				remove_action( 'woocommerce_mnm_child_item_details', 'wc_mnm_template_child_item_price', 				65, 2 );
			} else {
				add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'add_plus_sign' ), 10, 2 );
			}
		}
	}

	/**
	 * Remove price for $0.
	 *
	 * @param obj $mnm_item WC_Product of child item
	 * @param obj WC_Mix_and_Match $product the parent container
	 */
	public static function restore_price_template( $mnm_item, $parent_product ) {
		if( $parent_product->get_meta( '_has_premium_contents' ) == 'yes' ) {
			if( $mnm_item->get_price() == 0 ) {
				add_action( 'woocommerce_mnm_child_item_details', 'wc_mnm_template_child_item_price', 				65, 2 );
			} else {
				remove_filter( 'woocommerce_get_price_html', array( __CLASS__, 'add_plus_sign' ), 10, 2 );
			}
		}
	}

	/**
	 * Add plus sign to premium product price.
	 *
	 * @param  bool
	 * @param  obj WC_Product_Mix_and_Match
	 * @return bool 
	 */
	public static function add_plus_sign( $price, $product ) {
		return sprintf( _x( '%s %s', '+ sign preceding price string', 'wc-mnm-premium-upcharge' ), '+', $price );
	}
}

add_action( 'woocommerce_mnm_loaded', array( 'WC_MNM_Premium_Upcharge', 'init' ) );