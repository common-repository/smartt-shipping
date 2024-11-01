<?php
/* Get Shipper Info from Smartt Shipping */
function sts_get_shipper_info()
{

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));

	$shippingInfo = array();

	if ($smart_shipping_setting) {
		$api_key = $smart_shipping_setting['api_key'];

		$smt_api_dataBase_records = maybe_unserialize(get_option('woocommerce_smartshipping_api_data'));
		if (!empty($smt_api_dataBase_records)) {
			$smt_api_GetShipperInfo = trim($smt_api_dataBase_records['GetShipperInfo']);
			if (!empty($smt_api_GetShipperInfo)) {
				$shippingInfo = json_decode($smt_api_GetShipperInfo);
			}
		}

		if (empty($shippingInfo)) {
			$args = array(
				'method' => 'GET',
				'timeout' => 300,
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'headers' => array('apikey' => $api_key)
			);

			$postcodeApiUrl = smart_shipping_api_url . "api/carrierapi/GetShipperInfo";
			$content = wp_remote_get($postcodeApiUrl, $args);
			$response = wp_remote_retrieve_body($content);
			$shippingInfo = json_decode($response);

			$smt_api_data_array['GetShipperInfo'] = json_encode($shippingInfo);
			update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);
		}
	}
	return $shippingInfo;
}

/* Get all packages from Smartt shipping  */
function sts_all_packages_not_default()
{
	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));

	$all_packages = array();
	$all_pack = array('0' => "Please select package");

	if ($smart_shipping_setting) {
		$api_key = $smart_shipping_setting['api_key'];

		$smt_api_dataBase_records = maybe_unserialize(get_option('woocommerce_smartshipping_api_data'));

		if (!empty($smt_api_dataBase_records)) {
			$smt_api_GetAllPackages = trim($smt_api_dataBase_records['GetAllPackages']);
			if (!empty($smt_api_GetAllPackages)) {
				$all_packages = json_decode($smt_api_GetAllPackages);
			}
		}

		/* if not found in db then fetch from api and save into db */
		if (empty($all_packages)) {
			$args1 = array(
				'method' => 'GET',
				'timeout' => 300,
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'headers' => array('apikey' => $api_key)
			);

			$Package_api_Url = smart_shipping_api_url . "api/carrierApi/GetAllPackages";
			$contents = wp_remote_get($Package_api_Url, $args1);
			$responsed = wp_remote_retrieve_body($contents);
			$packages = json_decode($responsed);
			$all_packages = $packages->Packages;

			$smt_api_data_array['GetAllPackages'] = json_encode($all_packages);
			update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);
		}

		if (!empty($all_packages)) {
			foreach ($all_packages as $pack) {
				$PackageID = $pack->PackageID;
				$PackageTypeName = $pack->PackageTypeName;
				if ($PackageTypeName == 'ENVELOPE' || $PackageTypeName == 'Purolator Express Envelope' || $PackageTypeName == 'Purolator Express Pack') {
					continue;
				}
				$all_pack[$PackageID] = __($PackageTypeName, 'woocommerce');
			}
		}
	}
	return $all_pack;
}

/* Get all packages from Smartt shipping  */
function sts_shipping_all_packages()
{

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));

	$all_packages = array();
	$all_pack = array('00' => "Please select package");

	if ($smart_shipping_setting) {
		$api_key = $smart_shipping_setting['api_key'];

		$smt_api_dataBase_records = maybe_unserialize(get_option('woocommerce_smartshipping_api_data'));

		if (!empty($smt_api_dataBase_records)) {
			$smt_api_GetAllPackages = trim($smt_api_dataBase_records['GetAllPackages']);
			if (!empty($smt_api_GetAllPackages)) {
				$all_packages = json_decode($smt_api_GetAllPackages);
			}
		}

		/* if not found in db then fetch from api and save into db */
		if (empty($all_packages)) {

			$args1 = array(
				'method' => 'GET',
				'timeout' => 300,
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'headers' => array('apikey' => $api_key)
			);

			$Package_api_Url = smart_shipping_api_url . "api/carrierApi/GetAllPackages";
			$contents = wp_remote_get($Package_api_Url, $args1);
			$responsed = wp_remote_retrieve_body($contents);
			$packages = json_decode($responsed);
			$all_packages = $packages->Packages;

			$smt_api_data_array['GetAllPackages'] = json_encode($all_packages);
			update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);
		}

		$pk = 1;
		if (!empty($all_packages)) {
			foreach ($all_packages as $pack) {
				$PackageID = $pack->PackageID;
				$PackageTypeName = $pack->PackageTypeName;
				$all_pack[$PackageID] = __($PackageTypeName, 'woocommerce');
				$pk++;
			}
		}
	}
	return $all_pack;
}

/* Get all products from Smartt shipping  */
function sts_shipping_all_products()
{

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$all_products = array();
	$all_product = array('00' => "Please select product");

	if ($smart_shipping_setting) {
		$api_key = $smart_shipping_setting['api_key'];
		$smt_api_dataBase_records = maybe_unserialize(get_option('woocommerce_smartshipping_api_data'));

		if (!empty($smt_api_dataBase_records)) {
			$smt_api_GetAllProducts = trim($smt_api_dataBase_records['GetAllProducts']);
			if (!empty($smt_api_GetAllProducts)) {
				$all_products = json_decode($smt_api_GetAllProducts);
			}
		}

		/* if not found in db then fetch from api and save into db */
		if (empty($all_products)) {
			$args2 = array(
				'method' => 'GET',
				'timeout' => 300,
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'headers' => array('apikey' => $api_key)
			);

			$Products_api_Url = smart_shipping_api_url . "api/carrierApi/GetAllProducts";
			$product_contents = wp_remote_get($Products_api_Url, $args2);
			$product_response = wp_remote_retrieve_body($product_contents);

			$products = json_decode($product_response);
			$all_products = $products->Products;

			$smt_api_data_array['GetAllProducts'] = json_encode($all_products);
			update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);
		}

		$pd = 1;
		if (!empty($all_products)) {
			foreach ($all_products as $products) {
				$product_ID = $products->ProductID;
				$ProductTypeName = $products->ProductName;

				$all_product[$product_ID] = __($ProductTypeName, 'woocommerce');
				$pd++;
			}
		}
	}
	return $all_product;
}

/* Get all products (non dangerous) from Smartt shipping  */
function sts_all_products_not_dangerous()
{

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$all_products = array();
	$all_product = array('00' => "Please select product");

	if ($smart_shipping_setting) {
		$api_key = $smart_shipping_setting['api_key'];
		$smt_api_dataBase_records = maybe_unserialize(get_option('woocommerce_smartshipping_api_data'));

		if (!empty($smt_api_dataBase_records)) {
			$smt_api_GetAllProducts = trim($smt_api_dataBase_records['GetAllProducts']);
			if (!empty($smt_api_GetAllProducts)) {
				$all_products = json_decode($smt_api_GetAllProducts);
			}
		}

		/* if not found in db then fetch from api and save into db */
		if (empty($all_products)) {

			$args2 = array(
				'method' => 'GET',
				'timeout' => 300,
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'headers' => array('apikey' => $api_key)
			);

			$Product_api_Url = smart_shipping_api_url . "api/carrierApi/GetAllProducts";
			$product_contents = wp_remote_get($Product_api_Url, $args2);
			$product_response = wp_remote_retrieve_body($product_contents);
			$products = json_decode($product_response);
			$all_products = $products->Products;
			$smt_api_data_array['GetAllProducts'] = json_encode($all_products);
			update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);
		}

		$pd = 1;
		if (!empty($all_products)) {
			foreach ($all_products as $products) {
				$product_ID = $products->ProductID;
				$ProductTypeName = $products->ProductName;
				$IsDangerous = $products->IsDangerous;
				if ($IsDangerous == 1) {
					continue;
				}
				$all_product[$product_ID] = __($ProductTypeName, 'woocommerce');
				$pd++;
			}
		}
	}
	return $all_product;
}

/* Get all products (dangerous) from Smartt shipping  */
function sts_shipping_all_products_dangerous()
{

	$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
	$all_products = array();
	$all_product = array('00' => "Please select product");

	if ($smart_shipping_setting) {
		$api_key = $smart_shipping_setting['api_key'];

		$smt_api_dataBase_records = maybe_unserialize(get_option('woocommerce_smartshipping_api_data'));
		if (!empty($smt_api_dataBase_records)) {
			$smt_api_GetAllProducts = trim($smt_api_dataBase_records['GetAllProducts']);
			if (!empty($smt_api_GetAllProducts)) {
				$all_products = json_decode($smt_api_GetAllProducts);
			}
		}

		/* if not found in db then fetch from api and save into db */
		if (empty($all_products)) {

			$args2 = array(
				'method' => 'GET',
				'timeout' => 300,
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'headers' => array('apikey' => $api_key)
			);

			$Products_api_Url = smart_shipping_api_url . "api/carrierApi/GetAllProducts";
			$product_contents = wp_remote_get($Products_api_Url, $args2);
			$product_response = wp_remote_retrieve_body($product_contents);

			$products = json_decode($product_response);

			$all_products = $products->Products;

			$smt_api_data_array['GetAllProducts'] = json_encode($all_products);
			update_option('woocommerce_smartshipping_api_data', $smt_api_data_array);
		}

		$pd = 1;

		if (!empty($all_products)) {
			foreach ($all_products as $products) {
				$product_ID = $products->ProductID;
				$ProductTypeName = $products->ProductName;
				$IsDangerous = $products->IsDangerous;
				if ($IsDangerous != 1) {
					$IsDangerous = 0;
				}

				$pro = $product_ID . '_' . $IsDangerous;

				$all_product[$pro] = __($ProductTypeName, 'woocommerce');
				$pd++;
			}
		}
	}
	return $all_product;
}