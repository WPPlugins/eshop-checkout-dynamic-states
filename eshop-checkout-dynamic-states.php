<?php defined('ABSPATH') or die("No direct access allowed");
/*
* Plugin Name:   eShop Checkout Dynamic States
* Plugin URI:	 http://usestrict.net/2012/09/eshop-dynamic-checkout-state-county-province/
* Description:   Dynamically load correct State/County/Province in Checkout forms according to selected country.
* Version:       1.3.4
* Author:        Vinny Alves
* Author URI:    http://www.usestrict.net
*
* License:       GNU General Public License, v2 (or newer)
* License URI:  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* Copyright (C) 2012 - 2013 www.usestrict.net, released under the GNU General Public License.
*/
define ('ESHOP_DYNAMIC_STATES_ABSPATH', plugin_dir_path(__FILE__));
define ('ESHOP_DYNAMIC_STATES_INCLUDES', ESHOP_DYNAMIC_STATES_ABSPATH . '/includes');
define ('ESHOP_DYNAMIC_STATES_DOMAIN', 'eshop-checkout-dynamic-states');
define ('ESHOP_DYNAMIC_STATES_INCLUDES_URL', plugins_url( ESHOP_DYNAMIC_STATES_DOMAIN . '/includes'));
define ('ESHOP_DYNAMIC_STATES_VERSION', '1.3.4');

class eShop_Checkout_Dynamic_States 
{
	protected $country_table;
	protected $states_table;
	protected $domain = ESHOP_DYNAMIC_STATES_DOMAIN;
	
	public function __construct()
	{
		global $wpdb, $blog_id;
		
		session_start();
		
		$this->country_table = $wpdb->prefix . 'eshop_countries';
		$this->states_table  = $wpdb->prefix . 'eshop_states';

		// Set up data upon activation
		register_activation_hook(__FILE__ , array(&$this,'install_data'));
		
		// Add JS script if not admin, ajax stuff otherwise.
		if (! is_admin())
		{
			wp_enqueue_script( $this->domain . '-total-storage', ESHOP_DYNAMIC_STATES_INCLUDES_URL . '/jquery.total-storage.min.js', array( 'jquery' ),  
							  ESHOP_DYNAMIC_STATES_VERSION);
			
			wp_enqueue_script( $this->domain . '-refresh-states', ESHOP_DYNAMIC_STATES_INCLUDES_URL . '/eshop-checkout-dynamic-states.js', array( $this->domain . '-total-storage' ),
					ESHOP_DYNAMIC_STATES_VERSION);
			
			$sel_fields = array('state' => NULL, 'ship_state' => NULL);
			
			if($_REQUEST)
			{
				$sel_fields['state']      = $_REQUEST['state'];
				$sel_fields['ship_state'] = $_REQUEST['ship_state'];
			}
			elseif ($_SESSION['addy'.$blog_id])
			{
				$sel_fields['state']      = $_SESSION['addy'.$blog_id]['state'];
				$sel_fields['ship_state'] = $_SESSION['addy'.$blog_id]['ship_state'];
			}
			
			// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
			wp_localize_script( $this->domain . '-refresh-states', 'eShopDynamicStates', array( 'ajaxurl'      => admin_url( 'admin-ajax.php' ),
																								'ajaxaction'   => $this->domain . '-refresh-states',
																								'method'       => 'GET',
																								'includes_url' => ESHOP_DYNAMIC_STATES_INCLUDES_URL,
																								'sel_fields'   => $sel_fields) 
			);
		}
		else 
		{
			$this->sync_data();
		}
		
		// Add ajax handling for non-logged in people
		add_action('wp_ajax_nopriv_' . $this->domain . '-refresh-states', array(&$this,'refresh_states'));
			
		// and for logged-in people
		add_action('wp_ajax_' . $this->domain . '-refresh-states', array(&$this,'refresh_states'));
	}
	
	
	/**
	 * Method: sync_data
	 * Description: a workaround for Automated updates that don't trigger register_activation_hooks
	 */
	function sync_data()
	{
		$version = get_option('eshop-checkout-dynamic-states-version');
		if (! $version || $version !== ESHOP_DYNAMIC_STATES_VERSION)
		{
			$this->install_data();
		}
	}
	
	
	/**
	 * Method: refresh_states()
	 * @param string $country_code
	 * Description: Ajax interface for refreshing the state list when country changes
	 */
	public function refresh_states()
	{
		global $wpdb;
		
		$country_code = $_REQUEST['country_code'];
		
		$sql =<<<EOF
select id as state_code, stateName as state_name 
from $this->states_table
where list = %s
order by stateName
EOF;

		$sql  = $wpdb->prepare($sql, $country_code);
		$rows = $wpdb->get_results($sql,ARRAY_A);
		
		$states = array();
		foreach ($rows as $row)
		{
			$name          = $row['state_name'];
			$states[$name] = $row['state_code'];
		}
		
		header("Content-type: application/json");

		$out['success'] = true;
        $out['data']    = $states;

        echo json_encode($out);
        exit; // WP requirement for ajax-related methods
	}
	
	/**
	 * @method  install_data
	 * @desc    Installs initial data
	 */
	public function install_data()
	{
		$this->_install_countries();
		$this->_install_states();
	}
	
	
	/**
	 * @method  _install_countries
	 * @desc    Installs Countries
	 */
	private function _install_countries()
	{
		global $wpdb;
		
		$sql = "select count(*) from " . $this->country_table . " where code = %s and country = %s";

		if (FALSE !== ($fh = gzopen(ESHOP_DYNAMIC_STATES_INCLUDES . '/data/countries.csv.gz', 'rb')))
		{
			$query = $wpdb->prepare($sql,$row[0],$row[1]);
			
			while (FALSE !== ($row = fgetcsv($fh,500)))
			{
				if (! $wpdb->get_var($query))
				{
					$wpdb->insert($this->country_table,array('code' => $row[0], 'country' => $row[1], 'list' => 1));
				}
			}	
		}
	}
	
	
	/**
	 * @method  _install_states
	 * @desc    Installs States
	 */
	private function _install_states()
	{
		global $wpdb;

		$sql = "select count(*) from " . $this->states_table . " where code = %s and stateName = %s and list = %s"; 
		
		if (FALSE !== ($fh = gzopen(ESHOP_DYNAMIC_STATES_INCLUDES . '/data/states.csv.gz', 'rb')))
		{
			while (FALSE !== ($row = fgetcsv($fh,500)))
			{
				$query = $wpdb->prepare($sql,$row[0],$row[1],$row[2]);
				
				if (! $wpdb->get_var($query))
				{
					$wpdb->insert($this->states_table,array('code' => $row[0], 'stateName' => $row[1], 'list' => $row[2]));
				}
			}
		}
		
		
		// Set the correct version in the DB
		update_option('eshop-checkout-dynamic-states-version',ESHOP_DYNAMIC_STATES_VERSION);

	}
}

$eShop_Checkout_Dynamic_States = new eShop_Checkout_Dynamic_States();


/* End of file eshop-checkout-dynamic-states.php */
/* Location: eshop-checkout-dynamic-states/eshop-checkout-dynamic-states.php */