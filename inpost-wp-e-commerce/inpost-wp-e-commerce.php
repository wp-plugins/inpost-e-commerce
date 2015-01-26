<?php
/**
 * Plugin Name: InPost WP e-commerce Plugin
 * Plugin URI: http://www.inpost.co.uk
 * Description: This plugin allows shoppers to pick an InPost location for their parcel. Requires WordPress e-Commerce.
 * Version: 1.2
 * Author: InPost, David Arthur
 * Author URI: http://www.inpost.co.uk
 * License: GPL2
 */
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) )
{
	exit; // Exit if accessed directly
}

if ( ! defined( 'INPOST_TABLE_NAME' ) )
{
	define('INPOST_TABLE_NAME', 'order_shipping_inpostparcels');
}
if ( ! defined( 'INPOST_DB_VERSION' ) )
{
	define('INPOST_DB_VERSION', '1.0.1');
}
if ( ! defined( 'INPOST_PLUGIN_FILE' ) )
{
	define('INPOST_PLUGIN_FILE', dirname(__FILE__));
}

if( ! class_exists( 'My_List_Parcel' ) )
{
	require_once(dirname(__FILE__) . '/includes/list_parcels.php');
}

register_activation_hook( __FILE__, 'inpost_install');

class inpostshipping
{
	var $internal_name, $name;
	private $inpost_db_version = '1.0.1';

	///
	// Constructor
	//
	// @return boolean Always returns true.
	//
	function inpostshipping()
	{
		$this->internal_name = "inpostshipping";
		$this->name = __( "InPost Shipping", 'ipsp' );
		$this->is_external = false;

		return true;
	}

	/**
	 * Returns i18n-ized name of shipping module.
	 *
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Returns internal name of shipping module.
	 *
	 * @return string
	 */
	function getInternalName() {
		return $this->internal_name;
	}

	///
	// generates row of table rate fields
	//
	private function output_row1( $shipping )
	{
		$currency = wpsc_get_currency_symbol();
		$class = ( $this->alt ) ? ' class="alternate"' : '';
		$this->alt = ! $this->alt;
		?>
		<tr>
			<td<?php echo $class; ?>>
				<div class="cell-wrapper">
					<input type="text" name="wpsc_shipping_inpostshipping_shipping[weight]" placeholder="25" value="<?php echo esc_attr( $shipping['weight'] ); ?>" size="4" />
				</div>
			</td>
			<td<?php echo $class; ?>>
				<div class="cell-wrapper">
					<small><?php echo esc_html( $currency ); ?></small>
					<input type="text" name="wpsc_shipping_inpostshipping_shipping[price]" value="<?php echo esc_attr( $shipping['price'] ); ?>" size="4" />
				</div>
			</td>
			<td<?php echo $class; ?>>
				<div class="cell-wrapper">
					<input type="text" name="wpsc_shipping_inpostshipping_shipping[sizea]" placeholder="8x38x64" value="<?php echo esc_attr( $shipping['sizea'] ); ?>" size="10" />
				</div>
			</td>
			<td<?php echo $class; ?>>
				<div class="cell-wrapper">
					<input type="text" name="wpsc_shipping_inpostshipping_shipping[sizeb]" placeholder="19x38x64" value="<?php echo esc_attr( $shipping['sizeb'] ); ?>" size="10" />
				</div>
			</td>
			<td<?php echo $class; ?>>
				<div class="cell-wrapper">
					<input type="text" name="wpsc_shipping_inpostshipping_shipping[sizec]" placeholder="41x38x64" value="<?php echo esc_attr( $shipping['sizec'] ); ?>" size="10" />
				</div>
			</td>
		</tr>
		<?php
	}

	///
	// generates row of table rate fields
	//
	private function output_row2( $shipping )
	{
		$class = ( $this->alt ) ? ' class="alternate"' : '';
		$this->alt = ! $this->alt;
		?>
		<tr>
			<td<?php echo $class; ?> colspan="2">
				<div class="cell-wrapper">
					<input type="text" name="wpsc_shipping_inpostshipping_shipping[api_url]" placeholder="Site URL included with API Key" default="25" value="<?php echo esc_attr( $shipping['api_url'] ); ?>" size="30" />
				</div>
			</td>
			<td<?php echo $class; ?> colspan="2">
				<div class="cell-wrapper">
					<input type="text" name="wpsc_shipping_inpostshipping_shipping[api_key]" placeholder="Long API Key" value="<?php echo esc_attr( $shipping['api_key'] ); ?>" size="40" />
				</div>
			</td>
		</tr>
		<?php
	}


	///
	// Returns HTML settings form. Should be a collection of <tr> elements containing two columns.
	//
	// @return string HTML snippet.
	//
	function getForm()
	{
		$this->alt = false;
		$layers = get_option( 'inpost_shipping_layers', array() );

		if(!isset($layers['weight']))
		{
			$layers['weight'] = '25';
		}
		if(!isset($layers['price']))
		{
			$layers['price'] = '';
		}
		if(!isset($layers['sizea']))
		{
			$layers['sizea'] = '8x38x64';
		}
		if(!isset($layers['sizeb']))
		{
			$layers['sizeb'] = '19x38x64';
		}
		if(!isset($layers['sizec']))
		{
			$layers['sizec'] = '41x38x64';
		}
		if(!isset($layers['api_url']))
		{
			$layers['api_url'] = 'https://api-uk.easypack24.net/';
		}
		if(!isset($layers['api_key']))
		{
			$layers['api_key'] = '';
		}
		ob_start();
		?>
		<tr>
			<td colspan='2'>
				<table>
					<thead>
						<tr>
							<th class="total-weight" title="<?php _e( 'You must enter the maximum weight here in kilos', 'ipsp' ); ?>">
							<?php _e( 'Max Wight', 'ipsp' ); ?><br /><small><?php _e( 'in kilos', 'ipsp' ); ?></small>
							</th>
							<th class="shipping"><?php _e( 'Shipping Price', 'ipsp' ); ?></th>
							<th class="shipping"><?php _e( 'Size A', 'ipsp' ); ?><br /><small><?php _e( 'in cms', 'ipsp' ); ?></small>
							</th>
							<th class="shipping"><?php _e( 'Size B', 'ipsp' ); ?><br /><small><?php _e( 'in cms', 'ipsp' ); ?></small>
							</th>
							<th class="shipping"><?php _e( 'Size C', 'ipsp' ); ?><br /><small><?php _e( 'in cms', 'ipsp' ); ?></small>
							</th>
						</tr>
					</thead>
					<tbody class="table-rate">
						<?php
						$this->output_row1($layers);
						?>
					<tr>
							<th class="shipping" colspan="2"><?php _e( 'API URL', 'ipsp' ); ?></th>
							<th class="shipping" colspan="2"><?php _e( 'API Key', 'ipsp' ); ?></th>
					</tr>
						<?php
						$this->output_row2($layers);
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	///
	// Saves shipping module settings.
	//
	// @return boolean Always returns true.
	//
	function submit_form()
	{
		if ( empty( $_POST['wpsc_shipping_inpostshipping_shipping'] ) )
			return false;

		$shippings = $_POST['wpsc_shipping_inpostshipping_shipping'];

		update_option( 'inpost_shipping_layers', $shippings );
		return true;
	}

	///
	// returns shipping quotes using this shipping module.
	//
	// @return array collection of rates applicable.
	//
	function getQuote()
	{
		global $wpdb, $wpsc_cart;

		$weight = wpsc_cart_weight_total();
		if (is_object($wpsc_cart))
		{
			$cart_total = $wpsc_cart->calculate_subtotal(true);
		}

		$layers = get_option('inpost_shipping_layers');

		if ($layers['weight'] != '' && $layers['price'] != '')
		{
			if ($weight > (float)$layers['weight'])
			{
				// TODO parcel is too heavy.
				return array( __( "Weight Rate", 'ipsp' ) => $layers['price'] );
			}

			return array( __( "Fixed Rate", 'ipsp' ) => (float)$layers['price'] );
		}
		else
		{
			return array( __( "Fixed Rate", 'ipsp' ) => 0.0);
		}
	}

	/**
	 * calculates shipping price for an individual cart item.
	 *
	 * @param object $cart_item (reference)
	 * @return float price of shipping for the item.
	 */
	function get_item_shipping(&$cart_item)
	{
		global $wpdb, $wpsc_cart;

		$unit_price = $cart_item->unit_price;
		$quantity   = $cart_item->quantity;
		$weight     = $cart_item->weight;
		$product_id = $cart_item->product_id;

		$uses_billing_address = false;
		foreach ($cart_item->category_id_list as $category_id)
		{
			$uses_billing_address = (bool)wpsc_get_categorymeta($category_id, 'uses_billing_address');
			if ($uses_billing_address === true)
			{
				break; /// just one true value is sufficient
			}
		}

		if (is_numeric($product_id) && (get_option('do_not_use_shipping') != 1))
		{
			if ($uses_billing_address == true)
			{
				$country_code = $wpsc_cart->selected_country;
			} else {
				$country_code = $wpsc_cart->delivery_country;
			}

			if ($cart_item->uses_shipping == true)
			{
				//if the item has shipping
				$additional_shipping = '';
				if (isset($cart_item->meta[0]['shipping']))
				{
					$shipping_values = $cart_item->meta[0]['shipping'];
				}
				if (isset($shipping_values['local']) && $country_code == get_option('base_country'))
				{
					$additional_shipping = $shipping_values['local'];
				} else {
					if (isset($shipping_values['international'])) {
						$additional_shipping = $shipping_values['international'];
					}
				}
				$shipping = $quantity * $additional_shipping;
			} else {
				//if the item does not have shipping
				$shipping = 0;
			}
		} else {
			//if the item is invalid or all items do not have shipping
			$shipping = 0;
		}
		return $shipping;
	}

} // Class

function myplugin_update_db_check()
{
	if (get_site_option( 'inpost_db_version' ) != INPOST_DB_VERSION)
	{
		inpost_install();
	}
}

add_action( 'plugins_loaded', 'myplugin_update_db_check' );

	///
	// inpost_install function
	// 
	// @brief The table for the parcels is created.
	// @params none
	// @return none
	// 
	function inpost_install()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'order_shipping_inpostparcels';

		if ( !$wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) )
		{
			// Create the table.
			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id int(11) unsigned NOT NULL auto_increment,
			order_id int(11) NOT NULL,
			parcel_id varchar(200) NOT NULL default '',
			parcel_status varchar(200) NOT NULL default '',
			parcel_detail text NOT NULL default '',
			parcel_target_machine_id varchar(200) NOT NULL default '',
			parcel_target_machine_detail text NOT NULL default '',
			sticker_creation_date TIMESTAMP NULL DEFAULT NULL,
			creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			file_name text NOT NULL default '',
			api_source varchar(3) NOT NULL default '',
			variables text NOT NULL default '',
			PRIMARY KEY  id  (id)
			) DEFAULT CHARSET=utf8;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
	
			add_option( "inpost_db_version", INPOST_DB_VERSION );
		}
		else
		{
			$installed_ver = get_option( "inpost_db_version" );

			if( $installed_ver != INPOST_DB_VERSION )
			{
				$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id int(11) unsigned NOT NULL auto_increment,
			order_id int(11) NOT NULL,
			parcel_id varchar(200) NOT NULL default '',
			parcel_status varchar(200) NOT NULL default '',
			parcel_detail text NOT NULL default '',
			parcel_target_machine_id varchar(200) NOT NULL default '',
			parcel_target_machine_detail text NOT NULL default '',
			sticker_creation_date TIMESTAMP NULL DEFAULT NULL,
			creation_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			file_name text NOT NULL default '',
			api_source varchar(3) NOT NULL default '',
			variables text NOT NULL default '',
			PRIMARY KEY  id  (id)
			) DEFAULT CHARSET=utf8;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );

				update_option("inpost_db_version", INPOST_DB_VERSION);
			}
		}
	}

// Load the required Java Script
add_action('wp_enqueue_scripts', 'add_my_js');

	function add_my_js()
	{
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( 'inpost-wp-e-commerce/inpost-wp-e-commerce.php' ) )
		{
			//plugin is activated
			//error_log('The plugin is active.');
			$assets_path = str_replace( array( 'http:', 'https:' ), '', plugins_url('/assets/js/checkout', __FILE__) );

			// NB I have added three things into the array to stop an
			// alert being shown to the user.
			wp_enqueue_script('checkout_js', $assets_path . '.js', array('jquery', 'admin-bar', 'wp-e-commerce'), '1.0', true);
		}

		//if(wpsc_is_checkout() == true)
		//{
		//}
	}

add_action('wpsc_inside_shopping_cart', 'add_thankyou_message');

///
// add
//
function add_thankyou_message()
{
	// Check the weight of the cart items
	$ret = check_weight();

	if($ret == true)
	{
		echo '<input type="hidden" id="inpost_can_use" name="inpost_can_use" value="1">';
	}
	else
	{
		echo '<input type="hidden" id="inpost_can_use" name="inpost_can_use" value="0">';
	}
	echo '<input type="hidden" id="inpost_mobile" name="inpost_mobile">';
	echo '<input type="hidden" id="inpost_machine" name="inpost_machine">';
	echo '<input type="hidden" id="inpost_size" name="inpost_size">';
}

add_action('wpsc_submit_checkout', 'my_checkout');

function my_checkout($args)
{
	// We can also save the details into the InPost parcel table.
	global $wpdb;

	if($_POST['inpost_machine'] != '')
	{
		// The user has selected the InPost shipping method we must
		// verfiy that the Mobile and Locker ID are filled.

		$parcel_size = $_SESSION['inpost_parcel_size'];

		$sql_data = array(
			'order_id'      => $args['purchase_log_id'],
			'parcel_status' => 'Prepared',
			'parcel_target_machine_id' => $_POST['inpost_machine'],
			'api_source'    => 'UK',
			'variables'     =>  $_POST['inpost_mobile'] .
			':' . $parcel_size .
			':' . $_POST['collected_data']['9'],
		);
		$wpdb->insert($wpdb->prefix . INPOST_TABLE_NAME, $sql_data);
	}
}
	///
	// check_weight function
	//
	// @return true or false.
	//
	function check_weight()
	{
		global $wpsc_cart;

		// Defaults used at the end.
		$parcelSize = 'A';
		$is_dimension = true;

		// Get the shipping method's configuration data.
		$config_data = get_option('inpost_shipping_layers');
		
		// Read the maximum weight
		$maxWeightFromConfig = (float)strtolower(trim($config_data['weight']));

		// Process the various possible product sizes.
		$maxDimensionFromConfigSizeA = explode('x',
			strtolower(trim($config_data['sizea'])));
		$maxWidthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[0]);
		$maxHeightFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[1]);
		$maxDepthFromConfigSizeA = (float)trim(@$maxDimensionFromConfigSizeA[2]);
    
		// flattening to one dimension
		$maxSumDimensionFromConfigSizeA = $maxWidthFromConfigSizeA +
			$maxHeightFromConfigSizeA + $maxDepthFromConfigSizeA;

		$maxDimensionFromConfigSizeB = explode('x', strtolower(trim($config_data['sizeb'])));
		$maxWidthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[0]);
		$maxHeightFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[1]);
		$maxDepthFromConfigSizeB = (float)trim(@$maxDimensionFromConfigSizeB[2]);
		// flattening to one dimension
		$maxSumDimensionFromConfigSizeB = $maxWidthFromConfigSizeB +
			$maxHeightFromConfigSizeB + $maxDepthFromConfigSizeB;
		
		$maxDimensionFromConfigSizeC = explode('x', strtolower(trim($config_data['sizec'])));
		$maxWidthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[0]);
		$maxHeightFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[1]);
		$maxDepthFromConfigSizeC = (float)trim(@$maxDimensionFromConfigSizeC[2]);
		
		// flattening to one dimension
		$maxSumDimensionFromConfigSizeC = $maxWidthFromConfigSizeC +
			$maxHeightFromConfigSizeC + $maxDepthFromConfigSizeC;

		// Check if any of the dimensions are not set up correctly.
		if($maxWidthFromConfigSizeA == 0 ||
			$maxHeightFromConfigSizeA == 0 ||
		       	$maxDepthFromConfigSizeA  == 0 ||
			$maxWidthFromConfigSizeB  == 0 ||
			$maxHeightFromConfigSizeB == 0 ||
			$maxDepthFromConfigSizeB  == 0 ||
			$maxWidthFromConfigSizeC  == 0 ||
			$maxHeightFromConfigSizeC == 0 ||
			$maxDepthFromConfigSizeC  == 0)
		{
			// bad format in admin configuration
			$is_dimension = false;
		}
    
		$maxSumDimensionsFromProducts = 0;

		// Go through the products and check their dimensions and
		// weights.
		// size=10 x 20 x 10 cm weight=.32

		// The e-commerce plugin saves weights as lbs.
		// Convert back to Kg.
		$weight = (wpsc_cart_weight_total() / 2.2046);

		if($weight > $maxWeightFromConfig)
		{
			$is_dimension = false;
		}

		foreach ( $wpsc_cart->cart_items as $cartitem )
		{
			if ($cartitem->uses_shipping == true)
			{
				$quantity   = $cartitem->quantity;

				$dimensions = get_product_meta($cartitem->product_id, 'product_metadata'); //The 'dimensions' meta doesn't exist.

				if ( isset( $dimensions[0]['dimensions'] ) )
				{
					$dimensions = $dimensions[0]['dimensions'];
            			}
				else
				{
					continue;
				}

				$length = $dimensions['length'];
				$width  = $dimensions['width'];
				$height = $dimensions['height'];

				$calc_width  = $width  * $quantity;
				$calc_height = $height * $quantity;
				$calc_length = $length * $quantity;

				if( $calc_width > $maxWidthFromConfigSizeC ||
					$calc_height > $maxHeightFromConfigSizeC ||
					$calc_length  > $maxDepthFromConfigSizeC)
				{
					$is_dimension = false;
				}
				$maxSumDimensionsFromProducts += $width + $height + $length;
				if($maxSumDimensionsFromProducts > $maxSumDimensionFromConfigSizeC)
				{
					$is_dimension = false;
				}
			}
		}
		
		if($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeA)
		{
			$parcelSize = 'A';
		}
		elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeB)
		{
			$parcelSize = 'B';
		}
		elseif($maxSumDimensionsFromProducts <= $maxDimensionFromConfigSizeC)
		{
			$parcelSize = 'C';
		}

		// Save the parcel size to the session for retrieval later
		$_SESSION['inpost_parcel_size'] = $parcelSize;

		//echo json_encode($is_dimension);
		return $is_dimension;
	}

$inpostshipping = new inpostshipping();
$wpsc_shipping_modules[$inpostshipping->getInternalName()] = $inpostshipping;
