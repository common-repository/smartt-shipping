<?php
/** woocommerce_shipping_init */
add_action('woocommerce_shipping_init', 'smart_shipping_method');
function smart_shipping_method()
{
	if (!class_exists('smart_shipping_method')) {
		class smart_shipping_method extends WC_Shipping_Method
		{

			public function __construct()
			{
				$this->id = 'smartshipping';
				$this->method_title = __('SMARTT Shipping', 'smartt-shipping');
				$this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
				$this->title = isset($this->settings['title']) ? $this->settings['title'] : __('SMARTT Shipping', 'smartt-shipping');
				$this->init();
			}

			function init()
			{
				$this->init_form_fields();
				$this->init_settings();
				add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
			}

			function init_form_fields()
			{
				$all_pack = sts_all_packages_not_default();
				$all_product = sts_all_products_not_dangerous();
				$all_mcountry = get_smart_all_country();
				$all_ca_mstate = get_smart_all_country_state();

				$weight_unit = get_option('woocommerce_weight_unit');
				$dimension_unit = get_option('woocommerce_dimension_unit');

				$description = '( <a href="javascript:void(0)" class="smart_shipping_refresh_info_link">CLICK HERE</a> ) If you changed ( packages, products, shipper info, SMARTT api key ) in your SMARTT Shipping account for fetch that info and use into this site. <img id="sync-loader" class="form-loader" src="'. PLUGIN_DIR_URL . 'assets/img/loading.gif">';

				$this->form_fields = array(
					'enabled' => array(
						'title' => __('Enable Shipping', 'smartt-shipping'),
						'type' => 'checkbox',
						'default' => 'yes'
					),

					'Default_mode' => array(
						'title' => __('Live/Test Mode', 'smartt-shipping'),
						'type' => 'select',
						'description' => __('Please select the mode.', 'smartt-shipping'),
						'default' => __('Sandbox', 'smartt-shipping'),
						'options' => array("Sandbox" => "Sandbox", "Production" => "Production"),
					),

					'api_key' => array(
						'title' => __('SMARTT API KEY', 'smartt-shipping'),
						'type' => 'text',
						'description' => __('Get API Key from your Account Manager', 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'smart_shipping_refresh_info' => array(
						'title' => __('Sync Info From SMARTT Shipping Account!', 'smart_shipping_refresh_info'),
						'type' => 'button',
						'class' => 'smart_shipping_refresh_info_btn',
						'description' => __($description, 'smartt-shipping'),
					),

					'title' => array(
						'title' => __('Title', 'smartt-shipping'),
						'type' => 'text',
						'description' => __('Title to be display on site', 'smartt-shipping'),
						'default' => __('SMARTT Shipping', 'smartt-shipping')
					),

					'display_rates' => array(
						'title' => __('Show the rates in the frontend', 'smartt-shipping'),
						'type' => 'checkbox',
						'description' => __('If unchecked, the rates will not be displayed on the frontend, but the backend process will still function for other shipping methods.', 'smartt-shipping'),
						'default' => 'yes'
					),

					'Courier_Express' => array(
						'title' => __('Courier/Express Only or Courier + LTL Truck carriers', 'smartt-shipping'),
						'type' => 'select',
						'description' => __('', 'smartt-shipping'),
						'default' => '3',
						'options' => array(
							'1' => __('LTL Truck carriers'),
							'2' => __('Courier/Express Only'),
							'3' => __('Courier + LTL Truck carriers')
						),
					),
					
					'company_name' => array(
						'title' => __('Company Name', 'smartt-shipping'),
						'type' => 'text',
						'description' => __('', 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'website' => array(
						'title' => __('Website', 'smartt-shipping'),
						'type' => 'text',
						'description' => __('', 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'sender_contact_number' => array(
						'title' => __('Sender Contact Number', 'smartt-shipping'),
						'type' => 'text',
						'description' => __("Used to coordinate pickup if the courier is outside attempting delivery.", 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'Default_lenght' => array(
						'title' => __('Default Length', 'smartt-shipping') . " ($dimension_unit)",
						'type' => 'number',
						'description' => __("If you do not enter a product length, the system will automatically use the default length.</br>Min length - 1 inch, Max length - 96 inch", 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'Default_height' => array(
						'title' => __('Default Height', 'smartt-shipping') . " ($dimension_unit)",
						'type' => 'number',
						'description' => __("If you do not enter a product height, the system will automatically use the default height.</br>Min height - 1 inch, Max height - 96 inch", 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'Default_width' => array(
						'title' => __('Default Width', 'smartt-shipping') . " ($dimension_unit)",
						'type' => 'number',
						'description' => __("If you do not enter a product width, the system will automatically use the default width.</br>Min width - 1 inch, Max width - 96 inch", 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'Default_weight' => array(
						'title' => __('Default Weight', 'smartt-shipping') . " ($weight_unit)",
						'type' => 'number',
						'description' => __("If you do not enter a product weight, the system will automatically use the default weight.</br>Min weight - 1 lbs, Max weight - 9999 lbs", 'smartt-shipping'),
						'default' => __('', 'smartt-shipping')
					),

					'Default_package' => array(
						'title' => __('Default Package', 'smartt-shipping'),
						'type' => 'select',
						'description' => __('If you do not select a package type, the system will automatically use the default package type.', 'smartt-shipping'),
						'default' => __('no', 'smartt-shipping'),
						'options' => $all_pack
					),

					'Default_product_name' => array(
						'title' => __('Default Product Name', 'smartt-shipping'),
						'type' => 'select',
						'description' => __('If you do not enter a product name, the system will automatically use the default product name.', 'smartt-shipping'),
						'default' => __('no', 'smartt-shipping'),
						'options' => $all_product
					),

					'Default_mcountry' => array(
						'title' => __('Default Manufacture Country', 'smartt-shipping'),
						'type' => 'select',
						'description' => __('If you do not select any country, the system will automatically use the default country.', 'smartt-shipping'),
						'default' => __('CA', 'smartt-shipping'),
						'options' => $all_mcountry
					),

					'Default_mstate' => array(
						'title' => __('Default Manufacture State', 'smartt-shipping'),
						'type' => 'select',
						'description' => __('If you do not select state, the system will automatically use the default state.', 'smartt-shipping'),
						'options' => $all_ca_mstate
					),

					'shipping_fee_type' => array(
						'title' => __('Shipping Fee Type', 'smartt-shipping'),
						'type' => 'select',
						'description' => __("", 'smartt-shipping'),
						'default' => 'fixed',
						'options' => array(
							'fixed' => __('Fixed','smartt-shipping'),
							'percentage' => __('Percentage','smartt-shipping')
						),
					),
					
					'shipping_fee_markup' => array(
						'title' => __('Shipping Fee Markup', 'smartt-shipping'),
						'type' => 'number',
						'description' => __("Add extra shipping fee onto the freight cost generated by the plugin so that a customer would see and pay a higher price at checkout.", 'smartt-shipping'),
						'default' => 0.00
					)
				);
			}

			public function calculate_shipping($package = array())
			{
				global $woocommerce;

				if ($package['destination']['postcode'] != '' && $package['destination']['city'] != '') {
					$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
					$api_key = $smart_shipping_setting['api_key'];
					$enabled = $smart_shipping_setting['enabled'];
					$display_rates = $smart_shipping_setting['display_rates'];

					if ($enabled == 'yes') {
						if ($display_rates == 'yes') {
							$currency = get_option('woocommerce_currency');
							$country = $package['destination']['country'];
							$postcode = $package['destination']['postcode'];
							$city_name = $package['destination']['city'];
							$state = $package['destination']['state'];

							$courier_express = $smart_shipping_setting['Courier_Express'];
							$weight_unit = get_option('woocommerce_weight_unit');
							$dimension_unit = get_option('woocommerce_dimension_unit');

							if ($country == 'CA' || $country == 'US') {
								$product_data = array();
								if ($country == 'CA') {
									$cust_country_id = 1;
								}
								if ($country == 'US') {
									$cust_country_id = 2;
								}

								foreach ($package['contents'] as $item_id => $values) {

									$quantity = $values['quantity'];
									$product_id = $values['product_id'];

									$variation_id = $width = $length = $height = $weight = 0;
									$product = wc_get_product($product_id);

									if ($product->has_dimensions()) {
										if ($product->get_type() == 'variation' || $product->get_type() == 'variable') {
											$variation_id = $values['variation_id'];
											$variation = new WC_Product_Variation($variation_id);
											$length = $variation->get_length();
											$width = $variation->get_width();
											$height = $variation->get_height();
										} else {
											$length = $product->get_length();
											$width = $product->get_width();
											$height = $product->get_height();
										}
									}

									if ($product->has_weight()) {
										if ($product->get_type() == 'variation' || $product->get_type() == 'variable') {
											$variation_id = $values['variation_id'];
											$variation = new WC_Product_Variation($variation_id);
											$weight = $variation->get_weight();
										} else {
											$weight = $product->get_weight();
										}
									}

									if ($length == '' || $length == 0) {
										$length = $smart_shipping_setting['Default_lenght'];
									}
									if ($width == '' || $width == 0) {
										$width = $smart_shipping_setting['Default_width'];
									}
									if ($height == '' || $height == 0) {
										$height = $smart_shipping_setting['Default_height'];
									}
									if ($weight == '' || $weight == 0) {
										$weight = $smart_shipping_setting['Default_weight'];
									}

									$smart_shipping_is_stackable = get_post_meta($product_id, '_is_stackable', true);
									if ($smart_shipping_is_stackable == '' || $smart_shipping_is_stackable == 'no') {
										$smart_shipping_is_stackable = 'false';
									}
									if ($smart_shipping_is_stackable == 'yes') {
										$smart_shipping_is_stackable = 'true';
									}

									$package_id = get_post_meta($product_id, '_select-package', true);
									if ($package_id == '' || $package_id == '00') {
										$package_id = $smart_shipping_setting['Default_package'];
									}

									$smart_shipping_product_ids = get_post_meta($product_id, '_select', true);
									$smart_product_id = explode('_', $smart_shipping_product_ids);
									$smart_shipping_product_id = $smart_product_id[0];

									if ($smart_shipping_product_id == '' || $smart_shipping_product_id == '00') {
										$smart_shipping_product_id = $smart_shipping_setting['Default_product_name'];
									}

									$shipment_contains_dg_items = array();
									$smart_shipping_is_dangerous = get_post_meta($product_id, '_is_dangerous', true);

									if ($smart_shipping_is_dangerous == '' || $smart_shipping_is_dangerous == 'no') {
										$smart_shipping_is_dangerous = 'false';
										$dgproductid = '';
										$dgweight = '';
									}

									if ($smart_shipping_is_dangerous == 'yes') {
										$smart_shipping_is_dangerous = 'true';
										$dgproductid = $smart_shipping_product_id;
										$dgweight = (int) $weight;
										$shipment_contains_dg_items[] = array(
											'DGProductID' => $dgproductid,
											'DGWeight' => $dgweight
										);
									}

									if ($weight_unit == 'lbs' || $weight_unit == 'g' || $weight_unit == 'oz') {

										$isimperial = 'true';
										if ($weight_unit == 'g') {
											$weight = $weight * 0.0022046;
										}
										if ($weight_unit == 'oz') {
											$weight = $weight * 0.0625;
										}
										if ($dimension_unit == 'in') {

										}
										if ($dimension_unit == 'cm') {
											$length = $length * 0.39370;
											$width = $width * 0.39370;
											$height = $height * 0.39370;
										}
										if ($dimension_unit == 'm') {
											$length = $length * 39.370;
											$width = $width * 39.370;
											$height = $height * 39.370;
										}
										if ($dimension_unit == 'yd') {
											$length = $length * 36;
											$width = $width * 36;
											$height = $height * 36;
										}
										if ($dimension_unit == 'mm') {
											$length = $length * 0.0393701;
											$width = $width * 0.0393701;
											$height = $height * 0.0393701;
										}
									}

									if ($weight_unit == 'kg') {
										$isimperial = 'false';
										if ($dimension_unit == 'cm') {

										}
										if ($dimension_unit == 'in') {
											$length = $length / 0.39370;
											$width = $width / 0.39370;
											$height = $height / 0.39370;
										}
										if ($dimension_unit == 'm') {
											$length = $length / 0.01;
											$width = $width / 0.01;
											$height = $height / 0.01;
										}
										if ($dimension_unit == 'yd') {
											$length = $length / 0.010936;
											$width = $width / 0.010936;
											$height = $height / 0.010936;
										}
										if ($dimension_unit == 'mm') {
											$length = $length / 10;
											$width = $width / 10;
											$height = $height / 10;
										}
									}

									$weight = round($weight);
									$length = round($length);
									$width = round($width);
									$height = round($height);

									if ($weight == 0) {
										$weight = 1;
									}
									if ($length == 0) {
										$length = 1;
									}
									if ($width == 0) {
										$width = 1;
									}
									if ($height == 0) {
										$height = 1;
									}

									$total_weight = $weight * $quantity;
									$product_data[] = array(
										'Quantity' => $quantity,
										'Width' => $width,
										'Length' => $length,
										'Height' => $height,
										'Weight' => $total_weight,
										'PackageId' => $package_id,
										'ProductId' => $smart_shipping_product_id,
										'IsStackable' => $smart_shipping_is_stackable,
										'IsDangerous' => $smart_shipping_is_dangerous,
										'ShipmentContainsDGItems' => $shipment_contains_dg_items
									);
								}

								// source address
								$res = sts_get_shipper_info();
								$account_info = $res->AccountInfo;
								$shipper_name = $account_info->ShipperName;
								$Address1 = $account_info->Address1;
								$zip_code = $account_info->ZipCode;
								$city_ID = $account_info->CityID;
								$state_ID = $account_info->StateID;
								$country_ID = $account_info->CountryID;

								$shipment_customers = array(
									array(
										'CustomerId' => 0,
										'CityId' => 0,
										'City' => $city_name,
										'StateId' => 0,
										'State' => $state,
										'countryId' => $cust_country_id,
										'Name' => 'SamrttShipping',
										'PostalCode' => $postcode,
										'IsShipFrom' => 'false'
									),
									array(
										'CustomerId' => 0,
										'CityId' => $city_ID,
										'StateId' => $state_ID,
										'countryId' => $country_ID,
										'Name' => $shipper_name,
										'PostalCode' => $zip_code,
										'IsShipFrom' => 'true',
										'IsBillTo' => 'false'
									)
								);

								$shipping_data = array(
									'IsImperial' => $isimperial,
									'ShipmentItems' => $product_data,
									'TermId' => 6,
									'ShipmentCustomers' => $shipment_customers,
									'Services' => array('ServiceId' => 15, 'IsSelected' => 'false'),
									'ShipmentTypeId' => 3,
									'IsAllServices' => 'true',
									'Fragile' => 'false',
									'SaturdayDelivery' => 'false',
									'NoSignatureRequired' => 'true',
									'ResidentialSignature' => 'false',
									'SpecialHandling' => 'false',
									'IsReturnShipment' => 'false',
									'DeclaredValue' => 0,
									'DropOff' => 'false',
									'RateResultTypeId' => $courier_express
								);

								$api_response = wp_remote_post(
									smart_shipping_api_url . 'api/carrierapi/GetCarrierRatesCheckoutPage',
									array(
										'timeout' => 60,
										'headers' => array('apikey' => $api_key),
										'body' => $shipping_data
									)
								);

								if (is_wp_error($api_response)) {
									return false;
								}

								$body = json_decode($api_response['body']);

								$success = $body->Success;
								if (empty($success)) {
									return false;
								}
								if ($success) {
									$carriers = $body->Carriers;
									$i = 0;

									$markup_type = $smart_shipping_setting['shipping_fee_type'];
									$markup_amt = $smart_shipping_setting['shipping_fee_markup'];

									$currency_usd_val = one_csd_to_one_Usd();
									$currency_cad_val = one_usd_to_one_cad();
									foreach ($carriers as $key => $vals) {

										$CarrierName = $vals->CarrierName;
										$ServiceName = $vals->ServiceName;
										$Price = $vals->Price;
										$IsPriceInUsd = $vals->IsPriceInUsd;

										if ($currency == 'USD') {
											if ($IsPriceInUsd != 1) {
												$Price = $Price * $currency_usd_val;
												$Price = number_format($Price, 2);
											}
										}
										if ($currency == 'CAD') {
											if ($IsPriceInUsd == 1) {
												$Price = $Price * $currency_cad_val;
												$Price = number_format($Price, 2);
											}
										}
										if( $markup_amt == '' || $markup_amt == NULL ){
											$markup_amt = 0;
										}

										if ($markup_type == 'percentage') {
											$percentAmount = ($markup_amt / 100) * $Price;
											$Price = $Price + $percentAmount;
											$Price = number_format($Price, 2);
										} else {
											$Price = $Price + $markup_amt;
											$Price = number_format($Price, 2);
										}

										$rate = array(
											'id' => $this->id . '-' . $i,
											'label' => $CarrierName . '-' . $ServiceName,
											'cost' => $Price,
											'calc_tax' => 'per_order'
										);

										$this->add_rate($rate);
										$i++;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}