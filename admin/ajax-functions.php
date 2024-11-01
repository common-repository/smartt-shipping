<?php
/** Get dispatch rates  */
add_action('wp_ajax_sts_get_disptach_rates', 'sts_get_disptach_rates');
function sts_get_disptach_rates()
{
	global $woocommerce;

	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}

	$order_id = sanitize_text_field($_POST['order_id']);
	$insurance = (isset($_POST['insurance'])) ? floatval(sanitize_text_field($_POST['insurance'])) : 0;
	$all_product_data = sanitizeData($_POST['all_product_data']);
	$weight_unit = get_option('woocommerce_weight_unit');
	$dimension_unit = get_option('woocommerce_dimension_unit');
	$shipping_date = sanitize_text_field($_POST['shipping_date']);
	$is_clearing_shipment = isset($_POST['is_clearing_shipment']) ? sanitize_text_field($_POST['is_clearing_shipment']) : '';
	$importerrecord = isset($_POST['importerrecord']) ? sanitize_text_field($_POST['importerrecord']) : '';
	$importexporttype = isset($_POST['importexporttype']) ? sanitize_text_field($_POST['importexporttype']) : '';
	$residential_delivery = sanitize_text_field($_POST['residential_delivery']);
	$power_tailgate_delivery = null;

	if (isset($_POST['power_tailgate_delivery'])) {
		$power_tailgate_delivery = sanitize_text_field($_POST['power_tailgate_delivery']);
	}

	$warehouse_id = isset($_POST['warehouse_id']) ? sanitize_text_field($_POST['warehouse_id']) : '';
	$services_items = array();
	$is_drop_off = sanitize_text_field($_POST['is_drop_off']);

	if ($residential_delivery == 'true') {
		$services_items[] = array(
			'ServiceId' => 8,
			'IsSelected' => $residential_delivery
		);
	}

	if ($power_tailgate_delivery == 'true') {
		$services_items[] = array(
			'ServiceId' => 5,
			'IsSelected' => $power_tailgate_delivery
		);
	}

	$delivery_signature_required = sanitize_text_field($_POST['delivery_signature_required']);
	$ship_value = isset($_POST['ship_value']) ? sanitize_text_field($_POST['ship_value']) : '';
	$shipbroker = isset($_POST['shipbroker']) ? sanitize_text_field($_POST['shipbroker']) : '';
	$shipment_contains_dg_items = array();
	$is_shipping_country = sanitize_text_field($_POST['is_shipping_country']);

	if ($is_shipping_country == 'CA') {
		$cust_country_id = 1;
	}

	if ($is_shipping_country == 'US') {
		$cust_country_id = 2;
	}

	$product_data = array();
	foreach ($all_product_data as $val) {

		$decode_data = json_encode($val);
		$decode_datas = json_decode($decode_data);

		$length = $decode_datas->length;
		$width = $decode_datas->width;
		$weight = $decode_datas->weight;
		$quantity = $decode_datas->quantity;
		$height = $decode_datas->height;
		$dangerous_goods = $decode_datas->dangerous_goods;
		$non_stackable = $decode_datas->non_stackable;

		if ($non_stackable == 'true') {
			$non_stackable = 'false';
		} else {
			$non_stackable = 'true';
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

		$get_packages = $decode_datas->get_packages;
		$shipstation_product = $decode_datas->shipstation_product;
		$shipment_contains_dg_items = array();

		if ($dangerous_goods == 'true') {
			$dgproductIds = $shipstation_product;
			$dgweight = (float) $weight;
			$shipment_contains_dg_items[] = array(
				'DGProductID' => $dgproductIds,
				'DGWeight' => $dgweight
			);
		}

		$total_weight = $weight * $quantity;
		$product_data[] = array(
			'Quantity' => (float) $quantity,
			'Width' => (float) $width,
			'Length' => (float) $length,
			'Height' => (float) $height,
			'Weight' => (float) $total_weight,
			'PackageId' => (float) $get_packages,
			'ProductId' => (float) $shipstation_product,
			'IsStackable' => $non_stackable,
			'IsDangerous' => $dangerous_goods,
			'ShipmentContainsDGItems' => $shipment_contains_dg_items
		);
	}

	$item_data = array();
	if (isset($_POST['is_item_data']) && !empty($_POST['is_item_data'])) {
		$is_item_data = filter_var_array($_POST['is_item_data'], FILTER_SANITIZE_STRING);

		foreach ($is_item_data as $isval) {
			$isdecode_data = json_encode($isval);
			$isdecode_datas = json_decode($isdecode_data);

			$is_description = $isdecode_datas->is_description;
			$is_quantity = $isdecode_datas->is_quantity;
			$is_price = $isdecode_datas->is_price;
			$is_weight = $isdecode_datas->is_weight;
			$is_mcountry = $isdecode_datas->is_mcountry;
			$is_currency = $isdecode_datas->is_currency;
			$is_tarrif_code = $isdecode_datas->is_tarrif_code;

			if ($is_mcountry != 'CA') {
				$is_mstate = null;
			} else {
				$is_mstate = $isdecode_datas->is_mstate;
			}
			$item_data[] = array(
				'Quantity' => (float) $is_quantity,
				'ProductName' => $is_description,
				'PackageName' => null,
				'Currency' => $is_currency,
				'Price' => (float) $is_price,
				'CountryOfManufacture' => $is_mcountry,
				'StateOfManufacture' => $is_mstate,
				'TariffCode' => $is_tarrif_code
			);
		}
	}

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$api_key = $smart_shipping_setting['api_key'];
	$enabled = $smart_shipping_setting['enabled'];

	if ($enabled == 'yes') {
		$order = wc_get_order($order_id);

		$order_data = $order->get_data();
		$ship_details = $order_data['shipping'];
		$ship_address = $ship_details['address_1'];
		$city = $ship_details['city'];
		$state = $ship_details['state'];
		$postcode = $ship_details['postcode'];

		$args = array(
			'method' => 'GET',
			'timeout' => 300,
			'user-agent' => $_SERVER['HTTP_USER_AGENT'],
			'headers' => array('apikey' => $api_key)
		);

		$postcodeApiUrl = smart_shipping_api_url . 'api/carrierapi/GetShipperInfo';
		$content = wp_remote_get($postcodeApiUrl, $args);
		$response = wp_remote_retrieve_body($content);

		$res = json_decode($response);

		$account_info = $res->AccountInfo;
		$ShipperName = $account_info->ShipperName;
		$Address1 = $account_info->Address1;
		$ZipCode = $account_info->ZipCode;
		$CityID = $account_info->CityID;
		$StateID = $account_info->StateID;
		$CountryID = $account_info->CountryID;

		if (!empty($warehouse_id)) {
			$CityID = get_post_meta($warehouse_id, 'sm_city_id', true);
			$StateID = get_post_meta($warehouse_id, 'sm_state_id', true);
			$CountryID = get_post_meta($warehouse_id, 'sm_country_id', true);
			$ZipCode = get_post_meta($warehouse_id, 'sm_postalCode', true);
			$ShipperName = get_post_meta($warehouse_id, 'sm_shipper_from', true);
		}

		$shipment_customers = array(
			array(
				'CustomerId' => 0,
				'CityId' => 0,
				'City' => $city,
				'StateId' => 0,
				'State' => $state,
				'countryId' => $cust_country_id,
				'Name' => 'SamrttShipping',
				'PostalCode' => $postcode,
				'IsShipFrom' => 'false'
			),
			array(
				'CustomerId' => 0,
				'CityId' => $CityID,
				'StateId' => $StateID,
				'countryId' => $CountryID,
				'Name' => $ShipperName,
				'PostalCode' => $ZipCode,
				'IsShipFrom' => 'true',
				'IsBillTo' => 'false'
			)
		);

		$shipping_data = array(
			'IsImperial' => $isimperial,
			'ShipmentItems' => $product_data,
			'TermId' => 6,
			'ShipmentCustomers' => $shipment_customers,
			'Services' => $services_items,
			'ShipmentTypeId' => 3,
			'IsAllServices' => 'true',
			'Fragile' => 'false',
			'SaturdayDelivery' => 'false',
			'NoSignatureRequired' => 'false',
			'ResidentialSignature' => $delivery_signature_required,
			'SpecialHandling' => 'false',
			'IsReturnShipment' => 'false',
			'DeclaredValue' => $insurance,
			'DropOff' => $is_drop_off,
			'CustomsBrokerName' => $is_clearing_shipment,
			'ImporterOfRecordName' => $importerrecord,
			'ImportExportType' => $importexporttype,
			'InternationalShipmentItems' => $item_data,
			'CI_IsWithABroker' => $shipbroker,
			'CI_IsTotalValueBelowFixAmount' => $ship_value
		);

		$api_response = wp_remote_post(
			smart_shipping_api_url . 'api/carrierapi/GetCarrierRates',
			array(
				'timeout' => 60,
				'headers' => array('apikey' => $api_key),
				'body' => $shipping_data
			)
		);

		$body = json_decode($api_response['body']);
		if (is_wp_error($api_response)) {
			$message = $body->Message;
			echo wp_send_json_error("<p class='color-red'>$message</p>");
			exit;
		}
		
		$success = $body->Success;
		if (empty($success)) {
			$message = $body->Message;
			echo wp_send_json_error("<p class='color-red'>$message</p>");
			exit;
		}
		if ($success) {
			$carriers = $body->Carriers;
			if (empty($carriers)) {
				echo wp_send_json_error("<p class='color-red'>No Result Found</p>");
				exit;
			} else {
				$shipping_method_carriers = array();
				$shipping_method_services = array();
				foreach ($order->get_items('shipping') as $item_id => $item) {
					$methods = explode('-', $item->get_method_title());
					$trimmed_carrier_name = trim($methods[0]);
					$trimmed_service_name = trim($methods[1]);
					$shipping_method_carriers[] = $trimmed_carrier_name;
					$shipping_method_services[] = $trimmed_service_name;
				}

				foreach ($carriers as $key => $vals) {
					$service_name = $vals->ServiceName;
					$carrier_name = $vals->CarrierName;
					
					if ($service_name != '' && $service_name != null) {
						if (in_array($carrier_name, $shipping_method_carriers) && in_array($service_name, $shipping_method_services)) {	
							echo wp_send_json_success($vals);
							exit;
						}
					}
				}

				// Show buy label manual table if service not found
				?>
				<table border='1' width='100%'>
					<tr>
						<th class='carrier-rate-rows'>Carrier Name</th>
						<th class='carrier-rate-rows'>Service Name</th>
						<th class='carrier-rate-rows'>Transit Days</th>
						<th class='carrier-rate-rows'>Price</th>
						<th class='carrier-rate-rows'>Action</th>
					</tr>
					<?php
					foreach ($carriers as $key => $vals) {
						?>
						<tr>
							<td class='carrier-rate-rows'>
								<?php echo $vals->CarrierName; ?>
							</td>
							<td class='carrier-rate-rows'>
								<?php echo $vals->ServiceName; ?>
							</td>
							<td class='carrier-rate-rows'>
								<?php echo $vals->TransitDays; ?>
							</td>
							<td class='carrier-rate-rows'>
								<?php echo '$' . $vals->Price; ?>
							</td>
							<td class='carrier-rate-rows'>
								<p class='create_label_button' style='cursor:pointer'
									onclick="smt_create_label('<?php echo $order_id; ?>','<?php echo $vals; ?>')">
									Buy Label</p>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
			<?php
			}
		} else {
			$message = $body->Message;
			echo wp_send_json_error("<p class='color-red'>$message</p>");
			exit;
		}
	}
	wp_die();
}

/* create dispatch */
add_action('wp_ajax_sts_create_dispatch', 'sts_create_dispatch');
function sts_create_dispatch()
{

	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}

	$order_id = sanitize_text_field($_POST['order_id']);
	$insurance = (isset($_POST['insurance'])) ? floatval(sanitize_text_field($_POST['insurance'])) : 0;
	$all_product_data = sanitizeData($_POST['all_product_data']);
	$SelectedCarrier = sanitizeData($_POST['SelectedCarrier']);
	$shipping_date = sanitize_text_field($_POST['shipping_date']);
	$shipping_start_time = sanitize_text_field($_POST['shipping_start_time']);
	$shipping_end_time = sanitize_text_field($_POST['shipping_end_time']);
	$warehouse_id = isset($_POST['warehouse_id']) ? sanitize_text_field($_POST['warehouse_id']) : '';
	$residential_delivery = sanitize_text_field($_POST['residential_delivery']);
	$weight_unit = get_option('woocommerce_weight_unit');
	$dimension_unit = get_option('woocommerce_dimension_unit');

	$services_items = array();
	if ($residential_delivery == 'true') {
		$services_items[] = array(
			'ServiceId' => 8,
			'IsSelected' => $residential_delivery
		);
	}

	$is_drop_off = sanitize_text_field($_POST['is_drop_off']);
	$special_instructions = sanitize_text_field($_POST['special_instructions']);
	$ship_value = isset($_POST['ship_value']) ? sanitize_text_field($_POST['ship_value']) : '';
	$shipbroker = isset($_POST['shipbroker']) ? sanitize_text_field($_POST['shipbroker']) : '';

	$shipment_contains_dg_items = array();
	$product_data = array();

	foreach ($all_product_data as $val) {
		$decode_data = json_encode($val);
		$decode_datas = json_decode($decode_data);
		$length = $decode_datas->length;
		$width = $decode_datas->width;
		$weight = $decode_datas->weight;
		$quantity = $decode_datas->quantity;
		$height = $decode_datas->height;
		$dangerous_goods = $decode_datas->dangerous_goods;
		$non_stackable = $decode_datas->non_stackable;

		if ($non_stackable == 'true') {
			$non_stackable = 'false';
		} else {
			$non_stackable = 'true';
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

		$get_packages = $decode_datas->get_packages;
		$shipstation_product = $decode_datas->shipstation_product;
		$shipment_contains_dg_items = array();

		if ($dangerous_goods == 'true') {
			$dgproductIds = $shipstation_product;
			$dgweight = (float) $weight;
			$shipment_contains_dg_items[] = array(
				'DGProductID' => $dgproductIds,
				'DGWeight' => $dgweight
			);
		}

		$total_weight = $weight * $quantity;
		$product_data[] = array(
			'Quantity' => (float) $quantity,
			'Width' => (float) $width,
			'Length' => (float) $length,
			'Height' => (float) $height,
			'Weight' => (float) $total_weight,
			'PackageId' => (float) $get_packages,
			'ProductId' => (float) $shipstation_product,
			'IsStackable' => $non_stackable,
			'IsDangerous' => $dangerous_goods,
			'ShipmentContainsDGItems' => $shipment_contains_dg_items
		);
	}

	$delivery_signature_required = sanitize_text_field($_POST['delivery_signature_required']);
	
	if (empty($SelectedCarrier['ServiceName'])) {
		$SelectedCarrier['ServiceName'] = $SelectedCarrier['CarrierName'];
	}

	/** For International order only */
	$is_shipping_country = sanitize_text_field($_POST['is_shipping_country']);
	if ($is_shipping_country == 'CA') {
		$cust_country_id = 1;
	}
	if ($is_shipping_country == 'US') {
		$cust_country_id = 2;
	}

	$item_data = array();
	if (isset($_POST['is_item_data']) && !empty($_POST['is_item_data'])) {
		$is_item_data = filter_var_array($_POST['is_item_data'], FILTER_SANITIZE_STRING);

		foreach ($is_item_data as $isval) {
			$isdecode_data = json_encode($isval);
			$isdecode_datas = json_decode($isdecode_data);

			$is_description = $isdecode_datas->is_description;
			$is_quantity = $isdecode_datas->is_quantity;
			$is_price = $isdecode_datas->is_price;
			$is_weight = $isdecode_datas->is_weight;
			$is_mcountry = $isdecode_datas->is_mcountry;
			$is_currency = $isdecode_datas->is_currency;
			$is_tarrif_code = $isdecode_datas->is_tarrif_code;
			$package_name = $isdecode_datas->get_package_names;

			if ($is_mcountry != 'CA') {
				$is_mstate = null;
			} else {
				$is_mstate = $isdecode_datas->is_mstate;
			}
			$item_data[] = array(
				'Quantity' => (float) $is_quantity,
				'ProductName' => $is_description,
				'PackageName' => $package_name,
				'Currency' => $is_currency,
				'Price' => (float) $is_price,
				'CountryOfManufacture' => $is_mcountry,
				'StateOfManufacture' => $is_mstate,
				'TariffCode' => $is_tarrif_code
			);
		}
	}

	$is_clearing_shipment = isset($_POST['is_clearing_shipment']) ? sanitize_text_field($_POST['is_clearing_shipment']) : '';
	$importerrecord = isset($_POST['importerrecord']) ? sanitize_text_field($_POST['importerrecord']) : '';
	$importexporttype = isset($_POST['importexporttype']) ? sanitize_text_field($_POST['importexporttype']) : '';

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));

	$order = wc_get_order($order_id);
	$order_data = $order->get_data();
	$api_key = $smart_shipping_setting['api_key'];
	$ship_details = $order_data['shipping'];
	$ship_billing_details = $order_data['billing'];
	$ship_address = $ship_details['address_1'];
	$city = $ship_details['city'];
	$state = $ship_details['state'];
	$postcode = $ship_details['postcode'];
	$country = $ship_details['country'];
	$first_name = $ship_details['first_name'];
	$last_name = $ship_details['last_name'];
	$email = $ship_billing_details['email'];
	$shippingCompany = $ship_details['company'];
	$phone = $ship_billing_details['phone'];
	$phone_length = strlen($phone);

	if ($phone_length > 10) {
		$message = "<p style='color:red'>Customer/Shippers phone number is invalid. It must be 10 characters.</p>";
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
		return false;
	}

	$customer_name = $first_name . ' ' . $last_name;
	$fisrt_lastName = $first_name . ' ' . $last_name;

	if ($shippingCompany != '') {
		$customer_name = $shippingCompany;
	}

	$args = array(
		'method' => 'GET',
		'timeout' => 300,
		'user-agent' => $_SERVER['HTTP_USER_AGENT'],
		'headers' => array('apikey' => $api_key)
	);

	$postcodeApiUrl = smart_shipping_api_url . 'api/carrierapi/GetShipperInfo';
	$content = wp_remote_get($postcodeApiUrl, $args);
	$response = wp_remote_retrieve_body($content);

	$res = json_decode($response);

	$account_info = $res->AccountInfo;
	$ShipperName = $account_info->ShipperName;
	$shipperAddress1 = $account_info->Address1;
	$shipperZipCode = $account_info->ZipCode;
	$shipperCityID = $account_info->CityID;
	$shipperStateID = $account_info->StateID;
	$shipperCountryID = $account_info->CountryID;
	$shipper_PrimaryPhone = $account_info->PrimaryPhone;
	$shipper_PrimaryPhone = str_replace('-', '', $shipper_PrimaryPhone);
	$shipper_EmailID = $account_info->EmailID;
	$CustomerShipperId = $account_info->CustomerShipperId;

	$ShipmentDate = $shipping_date;
	$ShipmentTime = $shipping_date;

	if ($shipping_start_time != '') {
		$starting_time = strtotime($shipping_start_time);
		$st_time = date('H:i:s', $starting_time);
		$ShipmentDate = $shipping_date . 'T' . $st_time;
	} else {
		$OpeningTime = $account_info->OpeningTime;
		if ($OpeningTime != '') {
			$starting_time = strtotime($OpeningTime);
			$st_time = date('H:i:s', $starting_time);
			$ShipmentDate = $shipping_date . 'T' . $st_time;
		}
	}

	if ($shipping_end_time != '') {
		$end_time = strtotime($shipping_end_time);
		$ed_time = date('H:i:s', $end_time);
		$ShipmentTime = $shipping_date . 'T' . $ed_time;
	} else {
		$ClosingTime = $account_info->ClosingTime;
		if ($ClosingTime != '') {
			$end_time = strtotime($ClosingTime);
			$ed_time = date('H:i:s', $end_time);
			$ShipmentTime = $shipping_date . 'T' . $ed_time;
		}
	}

	$address = array(
		'cityName' => $city,
		'stateName' => $state,
		'countryName' => $country,
		'address' => '',
		'postalCode' => $postcode
	);
	$get_address_api = wp_remote_post(
		smart_shipping_api_url . 'api/carrierapi/ValidateAddressDetail',
		array(
			'timeout' => 60,
			'headers' => array('apikey' => $api_key),
			'body' => $address
		)
	);

	$get_address_api_body = json_decode($get_address_api['body']);
	$get_address_Success = $get_address_api_body->Success;

	if (empty($get_address_Success)) {
		return false;
	}

	if ($get_address_Success) {
		$get_address_data = $get_address_api_body->Data;

		$CityId = $get_address_data[0]->CityId;
		$StateId = $get_address_data[0]->StateId;
		$CountryId = $get_address_data[0]->CountryId;

		$check_customer_api = array(
			'method' => 'GET',
			'timeout' => 300,
			'user-agent' => $_SERVER['HTTP_USER_AGENT'],
			'headers' => array('apikey' => $api_key)
		);

		$postcodeApiUrl = smart_shipping_api_url . 'api/carrierapi/GetAllCustomers?Search=' . $email;
		$check_customer = wp_remote_get($postcodeApiUrl, $check_customer_api);
		$check_customer_response = wp_remote_retrieve_body($check_customer);
		$check_customer_data = json_decode($check_customer_response);

		$check_customer_success = $check_customer_data->Success;
		if ($check_customer_success) {
			$customer_exist = 'no';
			$check_all_customer = $check_customer_data->Customers;

			foreach ($check_all_customer as $value) {

				$stored_CustomerName = $value->CustomerName;
				$stored_Address = $value->Address;
				$stored_CityID = $value->CityID;
				$stored_StateID = $value->StateID;
				$stored_CountryID = $value->CountryID;
				$stored_ZipCode = $value->ZipCode;
				$stored_PrimaryPhone = $value->PrimaryPhone;

				if (
					strtolower($stored_CustomerName) == strtolower($customer_name) &&
					$stored_Address == $ship_address &&
					$stored_CityID == $CityId &&
					$stored_CountryID == $CountryId &&
					$stored_ZipCode == $postcode &&
					$stored_PrimaryPhone == $phone
				) {

					$customer_exist = 'yes';
					$CustomerID = $value->CustomerID;
					$customer_name = $value->CustomerName;
					$ship_address = $value->Address;
					$CityId = $value->CityID;
					$StateId = $value->StateID;
					$CountryId = $value->CountryID;
					$postcode = $value->ZipCode;
					$email = $value->EmailID;
					$phone = $value->PrimaryPhone;

					break;
				}
			}

			if ($customer_exist != 'yes') {
				$add_address = array(
					'CustomerID' => 0,
					'CustomerName' => $customer_name,
					'Address' => $ship_address,
					'CityID' => $CityId,
					'StateID' => $StateId,
					'countryId' => $CountryId,
					'ZipCode' => $postcode,
					'EmailID' => $email,
					'PrimaryPhone' => $phone,
					'SpecialInstructions' => '',
					'BrokerName' => 'SmarttShipping',
					'SecondaryPhone' => '',
					'Website' => '',
					'ContactName' => $fisrt_lastName,
					'OpeningTime' => '',
					'ClosingTime' => '',
					'AccountNo' => '',
					'PrimaryContactPosition' => '',
					'PrimaryContactPhone' => '',
					'SecondaryContactName' => '',
					'SecondaryContactPosition' => '',
					'AdditionalNotes' => '',
					'IsActive' => 'true',
					'Notify' => 'true',
					'PhoneExtension' => ''
				);

				$add_customer_api = wp_remote_post(
					smart_shipping_api_url . 'api/carrierapi/AddOrEditCustomer',
					array(
						'timeout' => 60,
						'headers' => array('apikey' => $api_key),
						'body' => $add_address
					)
				);

				$body = json_decode($add_customer_api['body']);

				$add_customer_success = $body->Success;
				if (empty($add_customer_success)) {
					return false;
				}

				if ($add_customer_success) {
					$get_generated_customer_data = $body->Customer;

					$CustomerID = $get_generated_customer_data->CustomerID;
					$customer_name = $get_generated_customer_data->CustomerName;
					$ship_address = $get_generated_customer_data->Address;
					$CityId = $get_generated_customer_data->CityID;
					$StateId = $get_generated_customer_data->StateID;
					$CountryId = $get_generated_customer_data->CountryID;
					$postcode = $get_generated_customer_data->ZipCode;
					$email = $get_generated_customer_data->EmailID;
					$phone = $get_generated_customer_data->PrimaryPhone;
				}
			}
		}
	}

	if (!empty($warehouse_id)) {

		$sm_street = get_post_meta($warehouse_id, 'sm_street', true);
		$sm_city_id = get_post_meta($warehouse_id, 'sm_city_id', true);
		$sm_state_id = get_post_meta($warehouse_id, 'sm_state_id', true);
		$sm_country_id = get_post_meta($warehouse_id, 'sm_country_id', true);
		$sm_shipper_from = get_post_meta($warehouse_id, 'sm_shipper_from', true);
		$sm_postalCode = get_post_meta($warehouse_id, 'sm_postalCode', true);
		$sm_customerid = get_post_meta($warehouse_id, 'sm_customerid', true);
		$sm_email = get_post_meta($warehouse_id, 'sm_email', true);
		$sm_phone = get_post_meta($warehouse_id, 'sm_phone', true);
		$shipment_customers = array(
			array(
				'CustomerId' => $CustomerID,
				'CityId' => $CityId,
				'StateId' => $StateId,
				'countryId' => $CountryId,
				'Name' => $customer_name,
				'Address' => $ship_address,
				'PostalCode' => $postcode,
				'Email' => $email,
				'Phone' => $phone,
				'IsShipFrom' => 'false'
			),
			array(
				'CustomerId' => $CustomerShipperId,
				'CityId' => $shipperCityID,
				'StateId' => $shipperStateID,
				'countryId' => $shipperCountryID,
				'Name' => $ShipperName,
				'Address' => $shipperAddress1,
				'PostalCode' => $shipperZipCode,
				'Email' => $shipper_EmailID,
				'Phone' => $shipper_PrimaryPhone,
				'IsBillTo' => 'true',
				'warehouse_id' => $warehouse_id
			),
			array(
				'CustomerId' => $sm_customerid,
				'CityId' => $sm_city_id,
				'StateId' => $sm_state_id,
				'countryId' => $sm_country_id,
				'Name' => $sm_shipper_from,
				'Address' => $sm_street,
				'PostalCode' => $sm_postalCode,
				'Email' => $sm_email,
				'Phone' => $sm_phone,
				'IsShipFrom' => 'true'
			)
		);
	} else {
		$shipment_customers = array(
			array(
				'CustomerId' => $CustomerID,
				'CityId' => $CityId,
				'StateId' => $StateId,
				'countryId' => $CountryId,
				'Name' => $customer_name,
				'Address' => $ship_address,
				'PostalCode' => $postcode,
				'Email' => $email,
				'Phone' => $phone,
				'IsShipFrom' => 'false'
			),
			array(
				'CustomerId' => $CustomerShipperId,
				'CityId' => $shipperCityID,
				'StateId' => $shipperStateID,
				'countryId' => $shipperCountryID,
				'Name' => $ShipperName,
				'Address' => $shipperAddress1,
				'PostalCode' => $shipperZipCode,
				'Email' => $shipper_EmailID,
				'Phone' => $shipper_PrimaryPhone,
				'IsBillTo' => 'true',
				'IsShipFrom' => 'true'
			)
		);
	}

	$create_dispatch = array(
		'IsImperial' => $isimperial,
		'ShipmentItems' => $product_data,
		'TermId' => 6,
		'ShipmentCustomers' => $shipment_customers,
		'Services' => $services_items,
		'ShipmentTypeId' => 3,
		'IsAllServices' => 'true',
		'Fragile' => 'false',
		'SaturdayDelivery' => 'false',
		'NoSignatureRequired' => 'false',
		'ResidentialSignature' => $delivery_signature_required,
		'SpecialHandling' => 'false',
		'DgClassId' => 1,
		'IsReturnShipment' => 'false',
		'DeclaredValue' => $insurance,
		'DropOff' => $is_drop_off,
		'ShipmentDate' => $ShipmentDate,
		'ShipmentTime' => $ShipmentTime,
		'PONumber' => $order_id,
		'SpecialInstruction' => $special_instructions,
		'SelectedCarrier' => $SelectedCarrier,
		'CustomsBrokerName' => $is_clearing_shipment,
		'ImporterOfRecordName' => $importerrecord,
		'ImportExportType' => $importexporttype,
		'InternationalShipmentItems' => $item_data,
		'CI_IsWithABroker' => $shipbroker,
		'CI_IsTotalValueBelowFixAmount' => $ship_value
	);

	$get_create_dispatch = wp_remote_post(
		smart_shipping_api_url . 'api/carrierapi/CreateDispatch',
		array(
			'timeout' => 60,
			'headers' => array('apikey' => $api_key),
			'body' => $create_dispatch
		)
	);

	$body = json_decode($get_create_dispatch['body']);

	if (is_wp_error($get_create_dispatch)) {
		$message = $body->Message;
		$message = "<p style='color:red' class='CreateDispatch'>$message</p>";
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}

	$success = $body->Success;
	if (empty($success)) {
		$message = $body->Message;
		$message = "<p style='color:red' class='CreateDispatch-second'>$message</p>";
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}

	if ($success) {
		$shipment_response = $body->ShipmentResponse;

		$tracking_number = $shipment_response->TrackingNumber;
		$smartt_bl_number = $shipment_response->SmarttBLNumber;
		$bol_path = $shipment_response->BolPath;
		$carrier_name = $shipment_response->CarrierName;
		$pickup_number = $shipment_response->PickUpNumber;
		$tracking_url = $shipment_response->TrackingUrl;
		$shipment_guid = $shipment_response->ShipmentGuid;
		$shipment_id = $shipment_response->ShipmentId;

		add_post_meta($order_id, 'Dispatch_created', 'yes');
		add_post_meta($order_id, 'Tracking_Number', $tracking_number);
		add_post_meta($order_id, 'Smartt_BL_Number', $smartt_bl_number);
		add_post_meta($order_id, 'Bol_Path', $bol_path);
		add_post_meta($order_id, 'Carrier_Name', $carrier_name);
		add_post_meta($order_id, 'PickUp_Number', $pickup_number);
		add_post_meta($order_id, 'Tracking_Url', $tracking_url);
		add_post_meta($order_id, 'current_shipping_price', $price);
		add_post_meta($order_id, 'ShipmentGuid', $shipment_guid);
		add_post_meta($order_id, 'shipping_date', $shipping_date);
		add_post_meta($order_id, 'ShipmentId', $shipment_id);

		$message = "<div class='tracking-details'>
			<span class='track-heading'>Tracking Number:  </span><span>$tracking_number</span></br>
			<span class='track-heading'>SMARTT BL Number: </span><span>$smartt_bl_number</span></br>
			<span class='track-heading'>Carrier Name: </span><span>$carrier_name</span></br>
			<span class='track-heading'>PickUp Number: </span><span>$pickup_number</span></br>
			<span class='track-heading'>Shipping Amount: </span><span>$$price</span></br>
			<div class='track-btn'>
				<a type='button' href='$tracking_url' target='_blank' class='button-primary'>Track Your Order</a>
				<a type='button' href='$bol_path' target='_blank' class='button-primary'>Download Label</a>
				<button type='button' class='button-primary' data-id='$order_id' id='cancel_shpment'>Cancel Shipment</button>
			</div>
		</div>";

		$msg = array('responses' => 'success', 'message' => $message);
		echo json_encode($msg);
	} else {
		$message = $body->Message;
		$message = "<p style='color:red'>$message</p>";
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
		return false;
	}
	wp_die();
}

function sanitizeData($data)
{
	return is_array($data) ? array_map('sanitizeData', $data) : htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/** smartt Shiping Get Dangerous Fields */
add_action('wp_ajax_sts_get_dangerous_fields', 'sts_get_dangerous_fields');
function sts_get_dangerous_fields()
{
	global $woocommerce;

	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}

	$dangerous_data = sanitize_text_field($_POST['dangerous_data']);
	$selected_id = sanitize_text_field($_POST['selected_id']);
	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$api_key = $smart_shipping_setting['api_key'];

	$args12 = array(
		'method' => 'GET',
		'timeout' => 300,
		'user-agent' => $_SERVER['HTTP_USER_AGENT'],
		'headers' => array('apikey' => $api_key)
	);

	$dangerousurl = smart_shipping_api_url . 'api/carrierapi/GetAllProducts?Search=' . $dangerous_data;
	$content = wp_remote_get($dangerousurl, $args12);
	$response = wp_remote_retrieve_body($content);
	$res = json_decode($response);
	$Productsdata = $res->Products;
	echo $Productsdata[0]->IsDangerous;

	wp_die();
}

/** smartt Shipping Package Value */
add_action('wp_ajax_sts_package_value', 'sts_package_value');
function sts_package_value()
{
	global $woocommerce;
	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}
	$smt_package_data = sanitize_text_field($_POST['smt_package_data']);
	$smt_length = sanitize_text_field($_POST['smt_length']);
	$smt_width = sanitize_text_field($_POST['smt_width']);
	$smt_height = sanitize_text_field($_POST['smt_height']);
	$smt_weight = sanitize_text_field($_POST['smt_weight']);

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$smt_api_key = $smart_shipping_setting['api_key'];

	$args12 = array(
		'method' => 'GET',
		'timeout' => 300,
		'user-agent' => $_SERVER['HTTP_USER_AGENT'],
		'headers' => array('apikey' => $smt_api_key)
	);

	$dangerousurl = smart_shipping_api_url . "api/carrierapi/GetAllPackages?Search=" . $smt_package_data;
	$content = wp_remote_get($dangerousurl, $args12);
	$response = wp_remote_retrieve_body($content);
	$results = json_decode($response);

	$package_data = $results->Packages;

	if (sizeof($package_data) > 0) {
		foreach ($package_data as $data) {
			$package_name = $data->PackageTypeName;
			if ($package_name == $smt_package_data) {
				$package_width = $data->width;
				$package_length = $data->Length;
				$package_height = $data->height;
				$package_weight = $data->Maxweight;
				if ($package_width != '' && $package_length != '' && $package_height != '') {
					$datas = array(
						'package_width' => $package_width,
						'package_length' => $package_length,
						'package_height' => $package_height,
						'package_weight' => $package_weight
					);

					$msg = array('responses' => 'success', 'data' => $datas);
					echo json_encode($msg);
					wp_die();
					return false;
				} else {
					$datas = array(
						'package_width' => $smt_width,
						'package_length' => $smt_length,
						'package_height' => $smt_height,
						'package_weight' => $smt_weight
					);
					$msg = array('responses' => 'No value assigned', 'data' => $datas);
					echo json_encode($msg);
					wp_die();
					return false;
				}
			} else {
				$datas = array(
					'package_width' => $smt_width,
					'package_length' => $smt_length,
					'package_height' => $smt_height,
					'package_weight' => $smt_weight
				);

				$msg = array('responses' => 'No value assigned', 'data' => $datas);
				echo json_encode($msg);
				wp_die();
				return false;
			}
		}
	} else {
		$datas = array(
			'package_width' => $smt_width,
			'package_length' => $smt_length,
			'package_height' => $smt_height,
			'package_weight' => $smt_weight
		);
		$msg = array(
			'responses' => 'No value assigned',
			'data' => $datas
		);
		echo json_encode($msg);
		wp_die();
		return false;
	}
	wp_die();
}

/** smart Shipping Cancel Shipment  */
add_action('wp_ajax_sts_cancel_shipment', 'sts_cancel_shipment');
function sts_cancel_shipment()
{
	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}
	$post_id = sanitize_text_field($_POST['post_id']);
	$shipment_id = get_post_meta($post_id, 'ShipmentId', true);
	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$api_key = $smart_shipping_setting['api_key'];
	$cancellation_reason = sanitize_text_field($_POST['cancellation_reason']);
	$cancel_shippment_data = array(
		'ShipmentId' => $shipment_id,
		'CancellationReasonNote' => $cancellation_reason
	);

	$cancel_shippment_apis = wp_remote_post(
		smart_shipping_api_url . 'api/carrierapi/CancelShipment',
		array(
			'timeout' => 60,
			'headers' => array('apikey' => $api_key),
			'body' => $cancel_shippment_data
		)
	);

	$body = json_decode($cancel_shippment_apis['body']);
	if (is_wp_error($cancel_shippment_apis)) {
		$message = $body->Message;
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}

	$success = $body->Success;
	if (empty($success)) {
		$message = $body->Message;
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}

	if ($success) {
		$message = $body->Message;
		delete_post_meta($post_id, 'Dispatch_created', '');
		delete_post_meta($post_id, 'Tracking_Number', '');
		delete_post_meta($post_id, 'Smartt_BL_Number', '');
		delete_post_meta($post_id, 'Bol_Path', '');
		delete_post_meta($post_id, 'Carrier_Name', '');
		delete_post_meta($post_id, 'PickUp_Number', '');
		delete_post_meta($post_id, 'Tracking_Url', '');
		delete_post_meta($post_id, 'current_shipping_price', '');
		delete_post_meta($post_id, 'ShipmentGuid', '');
		delete_post_meta($post_id, 'shipping_date', '');
		delete_post_meta($post_id, 'ShipmentId', '');

		$msg = array('responses' => 'success', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	} else {
		$message = $body->Message;
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}
}

/* sync Info From Smartt Shipping */
add_action('wp_ajax_sync_info_from_smartt_shipping', 'sts_sync_info_from_smartt_shipping');
function sts_sync_info_from_smartt_shipping()
{
	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}

	$shipping_enabled = $_POST['shipping_enabled'];
	$default_mode = $_POST['default_mode'];
	$api_key = $_POST['api_key'];

	if ($default_mode == 'Sandbox') {
		$smart_shipping_api_url = 'https://api.sandbox.smarttshipping.ca/';
	} else {
		$smart_shipping_api_url = 'https://smarttshipping.ca/';
	}
	
	$shipping_options = get_option('woocommerce_smartshipping_settings');

	if(!empty($shipping_options)){
		$shipping_options['enabled'] = $shipping_enabled;
		$shipping_options['Default_mode'] = $default_mode;
		$shipping_options['api_key'] = $api_key;

		update_option( 'woocommerce_smartshipping_settings', $shipping_options );

	} else {
		$smartt_shipping_options = array(
			'enabled' => $shipping_enabled,
			'Default_mode' => $default_mode,
			'api_key' => $api_key
		);

		update_option( 'woocommerce_smartshipping_settings', $smartt_shipping_options );
	}

	/* Add Shipping info into db */
	$shippingInfo = array();
	$args = array(
		'method' => 'GET',
		'timeout' => 300,
		'user-agent' => $_SERVER['HTTP_USER_AGENT'],
		'headers' => array('apikey' => $api_key)
	);

	$postcodeApiUrl = $smart_shipping_api_url . 'api/carrierapi/GetShipperInfo';
	$content = wp_remote_get($postcodeApiUrl, $args);
	$response = wp_remote_retrieve_body($content);
	$shippingInfo = json_decode($response);
	$smt_api_data_array['GetShipperInfo'] = json_encode($shippingInfo);
	update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);

	/* add package info into db */
	$all_packages = array();
	$args1 = array(
		'method' => 'GET',
		'timeout' => 300,
		'user-agent' => $_SERVER['HTTP_USER_AGENT'],
		'headers' => array('apikey' => $api_key)
	);
	$Package_api_Url = $smart_shipping_api_url . 'api/carrierApi/GetAllPackages';
	$contents = wp_remote_get($Package_api_Url, $args1);
	$package_response = wp_remote_retrieve_body($contents);
	$packages = json_decode($package_response);
	$all_packages = $packages->Packages;
	$smt_api_data_array['GetAllPackages'] = json_encode($all_packages);
	update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);

	/* add product info into db */
	$all_products = array();
	$args2 = array(
		'method' => 'GET',
		'timeout' => 300,
		'user-agent' => $_SERVER['HTTP_USER_AGENT'],
		'headers' => array('apikey' => $api_key)
	);
	$Product_api_Url = $smart_shipping_api_url . 'api/carrierApi/GetAllProducts';
	$product_contents = wp_remote_get($Product_api_Url, $args2);
	$product_response = wp_remote_retrieve_body($product_contents);
	$products = json_decode($product_response);
	$all_products = $products->Products;

	$smt_api_data_array['GetAllProducts'] = json_encode($all_products);
	update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);
	
	if( empty($all_packages) || empty($all_products) ){
		echo wp_send_json_error();
	} else {
		$data = array(
			'packages' => $all_packages,
			'products' => $all_products
		);
	
		echo wp_send_json_success($data);
	}
	exit;
}

/* save Preferred Carriers */
add_action('wp_ajax_sts_save_preferred_carriers', 'sts_save_preferred_carriers');
function sts_save_preferred_carriers()
{
	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}
	$preferred_carriers_data = filter_var_array($_POST['preferred_carriers'], FILTER_SANITIZE_STRING);
	$preferred_carriers = array();

	foreach ($preferred_carriers_data as $isval) {
		$isdecode_data = json_encode($isval);
		$isdecode_datas = json_decode($isdecode_data);

		$ServiceProductName = str_replace('&#39;', "'", $isdecode_datas->ServiceProductName);
		$ServiceProductKey = str_replace('&#39;', "'", $isdecode_datas->ServiceProductKey);
		$CarrierName = str_replace('&#39;', "'", $isdecode_datas->CarrierName);

		$CarrierId = $isdecode_datas->CarrierId;
		$IsSelected = $isdecode_datas->IsSelected;

		$preferred_carriers[] = array(
			'ServiceProductName' => stripslashes($ServiceProductName),
			'ServiceProductKey' => stripslashes($ServiceProductKey),
			'CarrierName' => stripslashes($CarrierName),
			'CarrierId' => $CarrierId,
			'IsSelected' => $IsSelected
		);
	}
	
	$preferred_carriers = json_encode($preferred_carriers);

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$api_key = $smart_shipping_setting['api_key'];

	$execute_eodm_apis = wp_remote_post(
		smart_shipping_api_url . 'api/carrierapi/SaveCarrierServiceOptionsSetting',
		array(
			'timeout' => 60,
			'headers' => array('apikey' => $api_key, 'Content-Type' => 'application/json; charset=utf-8'),
			'body' => $preferred_carriers,
			'data_format' => 'body',
		)
	);
	
	$body = json_decode($execute_eodm_apis['body']);
	if (is_wp_error($execute_eodm_apis)) {
		$message = $body->Message;
		$message = "<p style='color:red'>$message</p>";
		$msg = array('status' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}

	$success = $body->Success;
	if ($success == true) {
		$message = 'Preferred carriers info successfully saved';
		$msg = array('status' => 'success', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	} else {
		$message = $body->ErrorMessage;
		$msg = array('status' => 'fail', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}
}

/*  Manifest api */
add_action('wp_ajax_get_manifest_data', 'get_manifest_data');
function get_manifest_data()
{
	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}
	$shipment_id = sanitize_text_field($_POST['shipment_id']);
	$abc = explode(',', $shipment_id);

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$api_key = $smart_shipping_setting['api_key'];
	$shipment_data = array('ShipmentIds' => $abc);

	$execute_eodm_apis = wp_remote_post(
		smart_shipping_api_url . 'api/carrierapi/ExecuteShipmentsEODM',
		array(
			'timeout' => 60,
			'headers' => array('apikey' => $api_key),
			'body' => $shipment_data
		)
	);

	$body = json_decode($execute_eodm_apis['body']);
	if (is_wp_error($execute_eodm_apis)) {
		$message = $body->Message;
		$message = "<p style='color:red'>$message</p>";
		$msg = array('responses' => 'error', 'message' => $message);
		echo json_encode($msg);
		wp_die();
	}

	$success = $body->Success;
	if ($success == true) {
		$Shipments = $body->Data;
		$Manifest = $Shipments->ManifestFile;
		$msg = array('responses' => 'success', 'manifest' => $Manifest);
		echo json_encode($msg);
	} else {
		$message = $body->message;
		$msg = array('responses' => 'fail', 'message' => $message);
		echo json_encode($msg);
	}
	wp_die();
}

// to get states by country
add_action('wp_ajax_sts_get_states_by_country', 'sts_get_states_by_country_callback');
function sts_get_states_by_country_callback()
{
	if (isset($_POST['country'])) {
		$country_code = sanitize_text_field($_POST['country']);
		$states = WC()->countries->get_states($country_code);

		wp_send_json($states);
	}
}

// to get states by country
add_action('wp_ajax_sts_warehouse_get_states_by_country', 'sts_warehouse_get_states_by_country_callback');
function sts_warehouse_get_states_by_country_callback()
{
	if (isset($_POST['country'])) {
		$country_code = get_country_code_by_name( $_POST['country'] );
		$states = WC()->countries->get_states($country_code);
		wp_send_json($states);
	}
}

// save warehouse address
add_action('wp_ajax_sts_save_warehouse_address', 'sts_save_warehouse_address_callback');
function sts_save_warehouse_address_callback(){
	// Check for nonce security      
	if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
		die();
	}

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$api_key = $smart_shipping_setting['api_key'];
	
	if(empty($api_key) || $api_key == ''){
		echo wp_send_json_error('Please setup Smarttshipping plugin settings');
		exit;
	}
	
	global $wpdb;
	$tablename = $wpdb->prefix . "posts";
	
    parse_str($_POST['formData'], $formData);

	$sm_shipper_from = isset($formData['sm_shipper_from']) ? sanitize_text_field($formData['sm_shipper_from']) : '';
	$sm_street = isset($formData['sm_street']) ? sanitize_text_field($formData['sm_street']) : '';
	$sm_country = isset($formData['sm_country']) ? sanitize_text_field($formData['sm_country']) : '';
	$sm_state = isset($formData['sm_state']) ? sanitize_text_field($formData['sm_state']) : '';
	$sm_city = isset($formData['sm_city']) ? sanitize_text_field(ucfirst($formData['sm_city'])) : '';
	$sm_postalCode = isset($formData['sm_postalCode']) ? sanitize_text_field(strtoupper($formData['sm_postalCode'])) : '';
	$sm_email = isset($formData['sm_email']) ? $formData['sm_email'] : '';
	$sm_phone = isset($formData['sm_phone']) ? sanitize_text_field($formData['sm_phone']) : '';

	$address = array(
		"cityName" => $sm_city,
		"stateName" => $sm_state,
		"countryName" => $sm_country,
		"address" => "",
		"postalCode" => $sm_postalCode
	);

	$get_address_api = wp_remote_post(
		smart_shipping_api_url . 'api/carrierapi/ValidateAddressDetail',
		array(
			'timeout' => 60,
			'headers' => array('apikey' => $api_key),
			'body' => $address
		)
	);

	$get_address_api_body = json_decode($get_address_api['body']);
	$get_address_success = $get_address_api_body->Success;

	if (!empty($get_address_success) && $get_address_success == 1) {

		$get_address_data = $get_address_api_body->Data;
		$CityId = $get_address_data[0]->CityId;
		$StateId = $get_address_data[0]->StateId;
		$CountryId = $get_address_data[0]->CountryId;

		$add_address = array(
			"CustomerID" => 0,
			"CustomerName" => $sm_shipper_from,
			"Address" => $sm_street,
			"CityID" => $CityId,
			"StateID" => $StateId,
			"CountryId" => $CountryId,
			"ZipCode" => $sm_postalCode,
			"EmailID" => $sm_email,
			"PrimaryPhone" => $sm_phone,
			"SpecialInstructions" => "",
			"BrokerName" => "richard",
			"SecondaryPhone" => "",
			"Website" => "test",
			"ContactName" => "Baljeet",
			"OpeningTime" => "",
			"ClosingTime" => "",
			"AccountNo" => "",
			"PrimaryContactPosition" => "",
			"PrimaryContactPhone" => "",
			"SecondaryContactName" => "",
			"SecondaryContactPosition" => "",
			"AdditionalNotes" => "",
			"IsActive" => "true",
			"Notify" => "true",
			"PhoneExtension" => ""
		);

		$add_customer_api = wp_remote_post(
			smart_shipping_api_url . 'api/carrierapi/AddOrEditCustomer',
			array(
				'timeout' => 60,
				'headers' => array('apikey' => $api_key),
				'body' => $add_address
			)
		);

		$body = json_decode($add_customer_api['body']);

		$add_customer_Success = $body->Success;
	
		if (!empty($add_customer_Success) && $add_customer_Success == 1) {
			$get_generated_customer_data = $body->Customer;
			$CustomerID = $get_generated_customer_data->CustomerID;
			
			$post_title = 'SMARTT warehouse address' . '' . $CustomerID;
			$post_name = 'Smartt-shipping-warehouse-address-' . '' . $CustomerID;
			$wpdb->insert(
				$tablename,
				array(
					"post_title" => $post_title,
					"post_status" => 'publish',
					"post_name" => $post_name,
					"post_type" => 'smartt-warehouse-add'
				)
			);

			$lastid = $wpdb->insert_id;

			update_post_meta($lastid, 'sm_shipper_from', $sm_shipper_from);
			update_post_meta($lastid, 'sm_street', $sm_street);
			update_post_meta($lastid, 'sm_city', $sm_city);
			update_post_meta($lastid, 'sm_city_id', $CityId);
			update_post_meta($lastid, 'sm_state', $sm_state);
			update_post_meta($lastid, 'sm_state_id', $StateId);
			update_post_meta($lastid, 'sm_country', $sm_country);
			update_post_meta($lastid, 'sm_country_id', $CountryId);
			update_post_meta($lastid, 'sm_postalCode', $sm_postalCode);
			update_post_meta($lastid, 'sm_email', $sm_email);
			update_post_meta($lastid, 'sm_phone', $sm_phone);
			update_post_meta($lastid, 'sm_customerid', $CustomerID);
			update_post_meta($lastid, 'sm_default_address', '0');

			echo wp_send_json_success();
			exit;
		} else {
			echo wp_send_json_error('Error in saving address, Please fill correct details.');
			exit;
		}
	} else {
		echo wp_send_json_error('Invalid address details');
		exit;
	}
}
