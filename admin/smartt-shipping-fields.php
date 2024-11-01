<?php
/** add_smart_shipping_method */
add_filter('woocommerce_shipping_methods', 'add_smart_shipping_method');
function add_smart_shipping_method($methods)
{
	$methods[] = 'smart_shipping_method';
	return $methods;
}

// Display custom Fields in product detail shipping meta box
add_action('woocommerce_product_options_shipping', 'sts_custom_fields');
function sts_custom_fields()
{
	global $post;
	$all_pack = sts_shipping_all_packages();
	$all_product = sts_shipping_all_products_dangerous();
	$all_countries = get_smart_all_country();

	$select_mcountry = get_post_meta($post->ID, '_select-mcountry', true);
	$all_states = get_smart_all_country_state($select_mcountry);

	if (!empty($all_states)) {
		$all_states = array('00' => "Please select manufacture state") + $all_states;
	} else {
		$all_states = array('00' => "Please select manufacture state");
	}

	woocommerce_wp_select(
		array(
			'id' => '_select',
			'label' => __('Product Name', 'smartt-shipping'),
			'class' => 'smt_custom_product',
			'options' => $all_product
		)
	);

	woocommerce_wp_select(
		array(
			'id' => '_select-package',
			'label' => __('Package Type', 'smartt-shipping'),
			'class' => 'smt_product_all_packages',
			'options' => $all_pack
		)
	);

	woocommerce_wp_select(
		array(
			'id' => '_select-mcountry',
			'label' => __('Manufacturing Country', 'smartt-shipping'),
			'class' => 'smt_product_manufacture_country',
			'options' => array('00' => "Please select manufacture country") + $all_countries
		)
	);

	woocommerce_wp_select(
		array(
			'id' => '_select-mstate',
			'label' => __('Manufacturing State', 'smartt-shipping'),
			'class' => 'smt_product_manufacture_state',
			'options' => $all_states
		)
	);

	woocommerce_wp_checkbox(
		array(
			'id' => '_is_stackable',
			'wrapper_class' => '',
			'label' => __('Is stackable', 'smartt-shipping'),
			'description' => __('Is stackable', 'smartt-shipping')
		)
	);

	$custom_attr = array('disabled' => 'disabled');
	woocommerce_wp_checkbox(
		array(
			'id' => '_is_dangerous',
			'wrapper_class' => '',
			'class' => 'smt_dangerous_product',
			'custom_attributes' => $custom_attr,
			'label' => __('Is Dangereous', 'smartt-shipping'),
			'description' => __('Is Dangereous', 'smartt-shipping')
		)
	);
}

// Save custom Fields of product meta box
add_action('woocommerce_process_product_meta', 'sts_custom_fields_save');
function sts_custom_fields_save($post_id)
{
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;

	$woocommerce_select = sanitize_text_field($_POST['_select']);
	if (!empty($woocommerce_select)) {
		update_post_meta($post_id, '_select', esc_attr($woocommerce_select));
	}
	$woocommerce_select_package = sanitize_text_field($_POST['_select-package']);
	if (!empty($woocommerce_select_package)) {
		update_post_meta($post_id, '_select-package', esc_attr($woocommerce_select_package));
	}

	$woocommerce_mcountry = sanitize_text_field($_POST['_select-mcountry']);
	if (!empty($woocommerce_mcountry)) {
		update_post_meta($post_id, '_select-mcountry', esc_attr($woocommerce_mcountry));
	}

	$woocommerce_mstate = sanitize_text_field($_POST['_select-mstate']);
	if (!empty($woocommerce_mstate)) {
		update_post_meta($post_id, '_select-mstate', esc_attr($woocommerce_mstate));
	}

	$woocommerce_checkbox = isset($_POST['_is_stackable']) ? 'yes' : 'no';
	update_post_meta($post_id, '_is_stackable', $woocommerce_checkbox);
	$woocommerce_checkbox2 = isset($_POST['_is_dangerous']) ? 'yes' : 'no';
	update_post_meta($post_id, '_is_dangerous', $woocommerce_checkbox2);
}

/* Smart shipping option in order page */
add_action('admin_init', 'sts_register_meta_box');
function sts_register_meta_box()
{
	add_meta_box('SMARTT Shipping', __('SMARTT Shipping', 'smartt-shipping'), 'sts_add_dispatch', 'shop_order', 'normal', 'default');
}
//Callback for add dispatch smartt shipping meta box
function sts_add_dispatch($order = 0)
{

	$order_id = $order->ID;
	$get_dispatched = get_post_meta($order_id, 'Dispatch_created', true);
	$tracking_number = get_post_meta($order_id, 'Tracking_Number', true);
	$smartt_bl_number = get_post_meta($order_id, 'Smartt_BL_Number', true);
	$bol_path = get_post_meta($order_id, 'Bol_Path', true);
	$carrier_name = get_post_meta($order_id, 'Carrier_Name', true);
	$pickup_number = get_post_meta($order_id, 'PickUp_Number', true);
	$tracking_Url = get_post_meta($order_id, 'Tracking_Url', true);
	$shipping_amount = get_post_meta($order_id, 'current_shipping_price', true);
	$shipping_date = get_post_meta($order_id, 'shipping_date', true);
	$current_date = date('Y-m-d');

	if ($get_dispatched == 'yes') {
		?>
		<div class='tracking-details'>
			<span class='track-heading'>Tracking Number: </span><span>
				<?php echo $tracking_number; ?>
			</span></br>
			<span class='track-heading'>SMARTT BL Number: </span><span>
				<?php echo $smartt_bl_number; ?>
			</span></br>
			<span class='track-heading'>Carrier Name: </span><span>
				<?php echo $carrier_name; ?>
			</span></br>
			<span class='track-heading'>PickUp Number: </span><span>
				<?php echo $pickup_number; ?>
			</span></br>
			<span class='track-heading'>Shipping Amount: </span><span>&#x24;
				<?php echo $shipping_amount; ?>
			</span></br>
			<div class='track-btn'>
				<a type="button" href='<?php echo $tracking_Url; ?>' target='_blank' class='button-primary'>Track Order</a>
				<a type="button" href='<?php echo $bol_path; ?>' target='_blank' class='button-primary'>Download Label</a>
				<?php if ($shipping_date >= $current_date) { ?>
					<button type="button" class='button-primary' data-id='<?php echo $order_id; ?>' id='cancel_shpment'>Cancel
						Shipment</button>
				<?php } ?>
			</div>
		</div>
		<?php
	} else {
		$order = wc_get_order($order);
		$order_data = $order->get_data();
		$ship_details = $order_data['shipping'];
		$shipping_country = $ship_details['country'];

		$all_packages = sts_shipping_all_packages();
		$all_product = sts_shipping_all_products();

		$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
		$api_key = $smart_shipping_setting['api_key'];
		$enabled = $smart_shipping_setting['enabled'];

		if ($api_key != '') {
			if ($enabled == 'yes') {
				if ($shipping_country == 'US' || $shipping_country == 'CA') {
					?>
					<div class='custom-shipping-form'>
						<div class='smt-overlay'>
							<div class='overlay-content'>
								<img class='loaders_img' src="<?PHP echo plugin_dir_url(__DIR__) . 'assets/img/loading.gif'; ?>">
							</div>
						</div>
						<div class="hidden-shipping-detail" style="display:none;">
							<div class='smt_custom_shipping_fields'>
								<div id='smt_all_dynanic_fields'>
									<?php
									$length = $width = $height = $weight = 0;
									$append_id = 1;
									foreach ($order->get_items() as $item_id => $item) {
										$product_id = $item->get_product_id();
										$product = $item->get_product();
										$quantity = $item->get_quantity();

										if ($product->has_dimensions()) {
											if ($product->get_type() == 'variation') {
												$variation_id = $item->get_variation_id();
												if ($variation_id > 0) {
													$variation = new WC_Product_Variation($variation_id);
													$length = $variation->get_length();
													$width = $variation->get_width();
													$height = $variation->get_height();
												}
											} else {
												$length = $product->get_length();
												$width = $product->get_width();
												$height = $product->get_height();
											}
										} else {
											$length = $smart_shipping_setting['Default_lenght'];
											$width = $smart_shipping_setting['Default_width'];
											$height = $smart_shipping_setting['Default_height'];
										}

										if ($product->has_weight()) {
											if ($product->get_type() == 'variation') {
												$variation_id = $item->get_variation_id();
												if ($variation_id > 0) {
													$variation = new WC_Product_Variation($variation_id);
													$weight = $variation->get_weight();
												}
											} else {
												$weight = $product->get_weight();
											}
										} else {
											$weight = $smart_shipping_setting['Default_weight'];
										}

										$select_package = get_post_meta($product_id, '_select-package', true);
										if (empty($select_package) || $select_package <= 00) {
											$select_package = $smart_shipping_setting['Default_package'];
										}

										$smart_shipping_product_id = get_post_meta($product_id, '_select', true);
										$smart_product_id = explode('_', $smart_shipping_product_id);
										$product_type = $smart_product_id[0];

										if (empty($product_type) || $product_type <= 00) {
											$product_type = $smart_shipping_setting['Default_product_name'];
										}

										$is_stackable = get_post_meta($product_id, '_is_stackable', true);
										$is_dangerous = get_post_meta($product_id, '_is_dangerous', true);
										?>
										<div class='smt-required-fields' id='required-data<?php echo $append_id; ?>'>
											<div class='input-length customs-required-fields'>
												<label>Length</label></br>
												<input type='number' name='length' id='length<?php echo $append_id; ?>'
													value='<?php echo $length; ?>'>
											</div>
											<div class='input-width customs-required-fields'>
												<label>Width</label></br>
												<td><input type='number' name='width' id='width<?php echo $append_id; ?>'
														value='<?php echo $width; ?>'></td>
											</div>
											<div class='input-height customs-required-fields'>
												<label>Height</label></br>
												<td><input type='number' name='height' id='height<?php echo $append_id; ?>'
														value='<?php echo $height; ?>'></td>
											</div>
											<div class='input-weight customs-required-fields'>
												<label>Weight</label></br>
												<td><input type='number' name='weight' id='weight<?php echo $append_id; ?>'
														value='<?php echo $weight; ?>'></td>
											</div>
											<div class='input-quantity customs-required-fields'>
												<label>Quantity</label></br>
												<td><input type='number' name='quantity' id='quantity<?php echo $append_id; ?>'
														value='<?php echo $quantity; ?>'></td>
											</div>
											<div class='select-packages customs-required-fieldss'>
												<label>Package type</label></br>
												<select id='smt_get_packages<?php echo $append_id; ?>' class='smt_get_packages' data='1'>
													<?php foreach ($all_packages as $key => $value) { ?>
														<option value='<?php echo $key; ?>' <?php if ($select_package == $key) {
															   echo 'selected';
														   } ?>>
															<?php echo $value; ?>
														</option>
													<?php } ?>
												</select>
											</div>
											<div class='select-ship-product customs-required-fieldss'>
												<label>Product type</label></br>
												<select id='smt_shipstation_product<?php echo $append_id; ?>' class='smt_shipstation_product'
													data='1'>
													<?php foreach ($all_product as $key => $values) { ?>
														<option value='<?php echo $key; ?>' <?php if ($product_type == $key) {
															   echo 'selected';
														   } ?>>
															<?php echo $values; ?>
														</option>
													<?php } ?>
												</select>
											</div>
											<div class='input-check-stackabe customs-required-fields'>
												<br>
												<input type='checkbox' id='non_stackable<?php echo $append_id; ?>' name='non_stackable'
													value='non_stackable' <?php if ($is_stackable == 'no') {
														echo 'checked';
													} ?>>
												<label for="non_stackable">Non-stackable</label>
											</div>
											<div class='input-check-dangerous customs-required-fields tooltip'>
												<br>
												<input type='checkbox' id='dangerous_goods<?php echo $append_id; ?>' name='dangerous_goods'
													value='dangerous_goods' disabled <?php if ($is_dangerous == 'yes') {
														echo 'checked';
													} ?>>
												<label for="dangerous_goods">Dangerous Goods</label>
												<span class='tooltiptext'>The Dangerous option will automatically selected when you select
													Dangerous Goods. </span>
											</div>
											<div class='delete-icon'>
												<label></label></br>
											</div>
										</div>
										<?php
										$append_id++;
									}
									?>
								</div>
							</div>
							<input type="hidden" name="order_id" id="order_id" value="<?php echo $order_id; ?>">
						</div>

						<div class="additional-information">
							<h2><b>ADDITIONAL INFORMATION</b></h2>
							<!-- required-additional-fields -->
							<div class="required-additional-fields">
								<div class="fields">
									<div class="input-insurance add-customs-required-fields">
										<div class="tooltip">
											<label>Buy Additional Insurance <img
													src="<?php echo plugin_dir_url(__DIR__) . 'assets/img/icons.png'; ?>"></label>
											<span class="tooltiptexts">Specify the value for which you want to insure the package in case of
												loss or damage. Not available for all carriers.</span>
										</div><br>
										<input type="text" name="insurance" id="smt_insurance" value="" style="width: 90%;">
									</div>
									<div class="input-shipping-date add-customs-required-fields">
										<label>Shipment Date</label></br>
										<input type="date" name="shipping_date" id="smt_shipping_date" min="<?php echo date("Y-m-d"); ?>"
											value="" style="width: 90%;">
									</div>
								</div>
								<div class="fields">
									<div class="input-shipping-date add-customs-required-fields">
										<label>Start Time</label></br>
										<input type="time" name="shipping_start_time" id="smt_shipping_start_time" step="60"
											style="width: 90%;">
									</div>
									<div class="input-shipping-time add-customs-required-fields">
										<label>End Time</label></br>
										<input type="time" name="shipping_end_time" id="smt_shipping_end_time" step="60"
											style="width: 90%;">
									</div>
								</div>
								<div class="input-shipping-date add-customs-required-fields">
									<label>Instructions</label></br>
									<textarea id="special_instructions" rows="2" style="width:95%" class="form-control"></textarea>
								</div>
							</div>
							<!-- additional-services -->
							<div class="additional-services">
								<label>Additional Services</label></br>
								<div class="additional-service-shipstation">
									<input type="checkbox" id="smt_residential_delivery" name="residential_delivery"
										value="residential_delivery">
									<label for="smt_residential_delivery"> Residential Delivery</label>
								</div>
								<div class="additional-service-shipstation">
									<input type="checkbox" id="smt_power_tailgate_delivery" name="power_tailgate_delivery"
										value="power_tailgate_delivery">
									<label for="smt_power_tailgate_delivery"> Power Tailgate Delivery</label>
								</div>
								<div class="additional-service-shipstation">
									<input type="checkbox" id="smt_delivery_signature_required" name="delivery_signature_required"
										value="delivery_signature_required">
									<label for="smt_delivery_signature_required"> Courier Delivery Signature Required</label>
								</div>
								<div class="additional-service-shipstation">
									<input type="checkbox" id="smt_is_drop_off" name="is_drop_off" value="is_drop_off">
									<label for="smt_is_drop_off">Drop Off</label>
								</div>
							</div>
							<div class="clr"></div>
							<!-- warehouse-address -->
							<div class="warehouse-address">
								<?php
								$ware_args = array(
									'post_type' => 'smartt-warehouse-add',
									'post_status' => 'publish',
								);

								$ware_data = get_posts($ware_args);
								if (!empty($ware_data)) {
									?>
									<div class="tooltip" style="display:block;">
										<label>Select warehouse address <img
												src="<?php echo plugin_dir_url(__DIR__) . 'assets/img/icons.png'; ?>"></label>
										<span class="tooltiptext">If you not select any warehouse then default warehouse address will use.
										</span>
									</div>
									<select id="warehouse_id" name="warehouse_id" class="select-warehouse-address">
										<option value="">--Select--</option>
										<?php
										foreach ($ware_data as $ware_key) {
											$ware_post_id = $ware_key->ID;
											$sm_shipper_from = get_post_meta($ware_post_id, 'sm_shipper_from', true);
											$sm_street = get_post_meta($ware_post_id, 'sm_street', true);
											$sm_city = get_post_meta($ware_post_id, 'sm_city', true);
											$sm_state = get_post_meta($ware_post_id, 'sm_state', true);
											$sm_country = get_post_meta($ware_post_id, 'sm_country', true);
											$sm_postalCode = get_post_meta($ware_post_id, 'sm_postalCode', true);
											$sm_customerid = get_post_meta($ware_post_id, 'sm_customerid', true);
											$sm_default_address = get_post_meta($ware_post_id, 'sm_default_address', true);
											?>
											<option value="<?php echo $ware_post_id; ?>">
												<?php echo $sm_shipper_from . ' ,' . $sm_street . ' ' . $sm_city . ' ' . $sm_state . ' ' . $sm_country . ' ' . $sm_postalCode; ?>
											</option>
										<?php } ?>
									</select>
								<?php } ?>
							</div> <br>
							<input type="hidden" name="is_shipping_country" id="smt_is_shipping_country"
								value="<?php echo $shipping_country; ?>">
						</div>
					</div>
					<?php if ($shipping_country == 'US') { ?>
						<div class="smt-usa-data">
							<div>
								<h2><b>ADD CUSTOMS DECLARATIONS</b></h2>
								<div class="shipmentunder">
									<p>Is the total value of your shipment under $800 USD? </p>
									<span class="shipvalueinternal"><input type="radio" name="ship_value_below_800" id="cform_chk_below_800" value="true" />Yes</span>
									<span class="shipvalueinternalabove"><input type="radio" id="cform_chk_above_800" name="ship_value_below_800" value="false" />No</span>
								</div>
							</div>
							<div class="smt-article-msg" id="smt-article-msg">
								<p>A De Minimis shipment commonly referred to as Section 321, allows for goods valued at $800 USD or less, to
									enter duty-free into the U.S. Under this legislation shipments of most commodities are also permitted to
									enter without formal entry made by a commercial customs broker.</p>
							</div>
							<div id="smt-clear-ship">
								<p>Do you wish to clear this shipment ? </p>
								<span class="clearshipinternal"><input type="radio" name="shipbroker" id="smt_shipbrokertrue" value="true" />With a broker</span> 
								<span class="clearshipinternalfalse"><input type="radio" name="shipbroker" id="smt_shipbrokerfalse" value="false" />Without a broker</span>
							</div>
							<div class="smt-shipexportimport">
								<div class="clearshippment shipment-record" id="smt-clearshippment">
									<label>Who’s clearing this shipment?</label></br>
									<input type="text" name="clearing_shipment" id="smt_is_clearing_shipment" value=""></br>
								</div>
								<div class="importrecordmain shipment-record">
									<label>Who’s the Importer Of Record?</label></br>
									<input type="text" name="importer_record" id="smt_importer_record" value=""></br>
								</div>
								<div class="importexportmain shipment-record">
									<label>Import Export Type*</label></br>
									<select id="smt_import_export_type">
										<option value="">Select Type</option>
										<option value="Temporary">Temporary</option>
										<option value="Permanent">Permanent</option>
										<option value="Repair">Repair</option>
										<option value="Return">Return</option>
									</select></br>
								</div>
							</div>
							<div class="smt-allitemsdatatable" style="overflow-x:auto;">
								<table class="table table-sm" id="smt_is_tbl_customers">
									<thead>
										<tr>
											<th>Product Name</th>
											<th>Currency</th>
											<th>Origin Country</th>
											<th>Origin State</th>
											<th>Tariff no.</th>
										</tr>
									</thead>
									<tbody id="smt_is_data_row_append">
										<?php
										$productitems = $order->get_items();
										$is_i = 1;
										foreach ($productitems as $item) {
											$product_id = $item->get_product_id();
											$is_product_name = $item->get_name();
											$_product = $item->get_product();
											$currency = get_option('woocommerce_currency');

											$select_mcountry = get_post_meta($product_id, '_select-mcountry', true);
											if (empty($select_mcountry) || $select_mcountry == '') {
												$select_mcountry = $smart_shipping_setting['Default_mcountry'];
											}

											$select_mstate = get_post_meta($product_id, '_select-mstate', true);
											if (empty($select_mstate) || $select_mstate == '') {
												$select_mstate = $smart_shipping_setting['Default_mstate'];
											}

											$is_quantity = $item['quantity'];
											$is_weight = $_product->get_weight();
											$is_price = $order->get_item_total($item);

											$get_smart_country_currency = get_smart_country_currency();
											$get_smart_all_country = get_smart_all_country();
											?>
											<tr id="is_main_all_data" class="itemdata<?php echo $is_i; ?>">
												<td>
													<input type="text" class="form-control form-control-sm" id="is_description<?php echo $is_i; ?>" name="is_description" value="<?php echo $is_product_name; ?>">
												</td>
												<td>
													<div class="input-group text-center">
														<select class="form-control form-control-sm" id="is_currency<?php echo $is_i; ?>"
															style="width: 70px !important;">
															<?php foreach ($get_smart_country_currency as $key => $value) { ?>
																<option value="<?php echo $key; ?>" <?php if ($currency == $key) {
																		echo "selected";
																	} ?>>
																	<?php echo $key; ?>
																</option>
															<?php } ?>
														</select>
													</div>
												</td>
												<td>
													<select class="form-control input-md form-control-sm" id="is_mcountry<?php echo $is_i; ?>"
														style="width:100%" name="is_mcountry" data-id="<?php echo $is_i; ?>">
														<?php foreach ($get_smart_all_country as $key => $value) { ?>
															<option value="<?php echo $key; ?>" <?php if ($select_mcountry == $key) {
																   echo "selected";
															   } ?>>
																<?php echo $value; ?>
															</option>
														<?php } ?>
													</select>
												</td>
												<td>
													<?php if ($select_mcountry == 'CA') {
														$get_smart_all_country_state = get_smart_all_country_state($select_mcountry);
														?>
														<select class="form-control input-md form-control-sm" id="is_mstate<?php echo $is_i; ?>"
															style="width:100%" name="is_mstate" data-id="<?php echo $is_i; ?>">
															<?php foreach ($get_smart_all_country_state as $key => $value) { ?>
																<option value="<?php echo $key; ?>" <?php if ($select_mstate == $key) {
																	   echo "selected";
																   } ?>>
																	<?php echo $value; ?>
																</option>
															<?php } ?>
														</select>
													<?php } else { ?>
														<select class="form-control input-md form-control-sm" id="is_mstate<?php echo $is_i; ?>"
															style="width:100%" name="is_mstate" data-id="<?php echo $is_i; ?>" disabled='true'>
															<option value="">Not required</option>
														</select>
													<?php } ?>
												</td>
												<td>
													<input type="text" name="is_tarrif_code" id="is_tarrif_code<?php echo $is_i; ?>" placeholder="" class="form-control form-control-sm" />
												</td>
												<td style="display:none;">
													<input type="number" style="width:100%" class="form-control form-control-sm" id="is_quantity<?php echo $is_i; ?>" name="is_quantity" value="<?php echo $is_quantity; ?>">
													<input type="number" style="width:100%" class="form-control form-control-sm" name="is_weight" id="is_weight<?php echo $is_i; ?>" value="<?php echo $is_weight; ?>">
													<input style="width: 45px!important;" name="is_price" id="is_price<?php echo $is_i; ?>" type="text" class="form-control thin-control form-control-sm" value="<?php echo $is_price; ?>">
												</td>
											</tr>
											<?php
											$is_i++;
										}
										?>
									</tbody>
									<tfoot>
										<tr>
											<td colspan="6" class="alert alert-warning">
												<p class="weightmsg">The weight of all items combined must not be higher than the parcel weight.</p>
											</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					<?php } ?>
					<div id="fetchratesdiv">
						<input type="button" class="button-primary custom" name="get_rate" id="smt_get_rates" value="Generate Shipment" />
					</div>
					<div class="table" id="smt_rate_table"></div>
					<div class="smt-shipping-error"></div>
					<?php
				} else {
					echo "<p class='color-red' >Shipping Methods only available in Canada & US.</p>";
				}
			} else {
				echo "<p class='color-red' >Please Enable the plugin from Settings.</p>";
			}
		} else {
			echo "<p class='color-red'>Please Insert SMARTT Shipping Api Key in WooCommerce->Settings->Shipping->SMARTT Shipping.</p>";
		}
	}
}