<?php
if (array_key_exists('sm_delete_warehouse', $_POST)) {
	$sm_warehouse_id = sanitize_text_field($_POST['sm_warehouse_id']);

	if (!empty($sm_warehouse_id)) {
		global $wpdb;

		$sm_customerId = get_post_meta($sm_warehouse_id, 'sm_customerid', true);
		$tablename = $wpdb->prefix . "posts";
		$warehouse_delete = $wpdb->delete(
			$tablename,
			array(
				"id" => $sm_warehouse_id,
				"post_type" => 'smartt-warehouse-add',
			)
		);

		if (!empty($warehouse_delete) && $warehouse_delete == 1) {
			$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
			$api_key = $smart_shipping_setting['api_key'];
			$delete_customer_api = wp_remote_post(
				smart_shipping_api_url . 'api/carrierapi/DeleteCustomer?id=' . $sm_customerId,
				array(
					'timeout' => 60,
					'headers' => array('apikey' => $api_key)
				)
			);
			$deleteResult = json_decode($delete_customer_api['body']);
			$delete_customer_Success = $deleteResult->Success;
			?>
			<div class="success notice">
				<p>
					<?php _e('Successfully deleted', 'woocommerce'); ?>
				</p>
			</div>
			<?php
		}
	}
}

$btn_value = 'Add Warehouse';
$ware_post_id = $sm_shipper_from = $sm_street = $sm_city = $sm_state = $sm_country = $sm_postalCode = $sm_customerid = $sm_default_address = $sm_email = $sm_phone = '';
$all_states = array();
?>

<div class="container ware-wrapper">
	<h3 class="ware-house-title"> Add New Warehouse Address </h3>
	<span style="font-size: 11px;"> ( Note: You can use the warehouse addresses as a third-party address on the order detail page. ) </span>
	<br>
	<form method="post" action=""  class="ware-house-form" name="add-warehouse-address" id="add-warehouse-address">
		<input type="hidden" name="sm_warehouse_id" id="sm_warehouse_id" value="<?php echo $ware_post_id; ?>">
		<div class="width100">
			<div class="width45 displayInLine">
				<label for="fname" class="ware-label">Shipper/From</label>
				<input type="text" class="width100" id="shipper_from" name="sm_shipper_from" value="<?php echo $sm_shipper_from; ?>" placeholder="Shipper/From">
			</div>
		</div>
		<br>
		<div class="width100">
			<div class="">
				<div class="width30 displayInLine">
					<label for="subject" class="ware-label">Email</label>
					<input type="email" class="width100" id="sm_email" name="sm_email" value="<?php echo $sm_email; ?>" placeholder="Email">
				</div>
				<div class="width30 displayInLine">
					<label for="subject" class="ware-label">Phone no.</label>
					<input type="text" class="width100" id="sm_phone" name="sm_phone" value="<?php echo $sm_phone; ?>" placeholder="Phone no">
				</div>
			</div>
		</div>
		<br>
		<div class="width100">
			<div class="width45 displayInLine">
				<label for="lname" class="ware-label">Street</label>
				<input type="text" class="width100" id="street" name="sm_street" value="<?php echo $sm_street; ?>" placeholder="Street">
			</div>
		</div>
		<br>
		<?php
			$all_countries = get_smart_all_country();
		?>
		<div class="width100">
			<div class="width30 displayInLine">
				<label for="subject" class="ware-label">Country</label>
				<select class="width100" id="country" name="sm_country">
					<option value="">--Select Country--</option>
					<?php foreach ($all_countries as $key => $value) { ?>
						<option value="<?php echo $value; ?>" <?php echo ($value == $sm_country) ? 'selected' : ''; ?>> <?php echo $value; ?> </option>
					<?php } ?>
				</select>
			</div>
			<div class="width30 displayInLine">
				<label for="subject" class="ware-label">State</label>
				<select class="width100" id="state" name="sm_state">
					<option value="">--Select State--</option>
					<?php foreach ($all_states as $key => $value) { ?>
						<option value="<?php echo $value; ?>" <?php echo ($value == $sm_state) ? 'selected' : ''; ?>> <?php echo $value; ?> </option>
					<?php } ?>
				</select>
			</div>
		</div>
		<br>
		<div class="width100">
			<div class="width30 displayInLine">
				<label for="country" class="ware-label">City</label>
				<input type="text" class="width100" id="city" name="sm_city" value="<?php echo $sm_city; ?>"  placeholder="City">
			</div>
			<div class="width30 displayInLine">
				<label for="subject" class="ware-label">Postal Code</label>
				<input type="text" class="width100" id="postalCode" name="sm_postalCode" value="<?php echo $sm_postalCode; ?>" placeholder="PostalCode">
			</div>
		</div>
		<br>
		<div class="width100">
			<input type="submit" name="sm_submit_key" id="sm_submit_key" class="button button-primary sm_submit_btn" value="<?php echo $btn_value; ?>">
			<div id="required-error" style="color:red; display:none;margin-bottom: 10px">Please fill all required (*) field </div>
			<div class='warehouse-overlay'>
				<img class='loaders_img warehouse-img' id="warehouse_loader" src="<?php echo PLUGIN_DIR_URL . 'assets/img/loading.gif'; ?>" style="display:none;">
			</div>
		</div>
	</form>
	<?php
	$ware_args = array(
		'post_type' => 'smartt-warehouse-add',
		'post_status' => 'publish',
	);
	$ware_data = get_posts($ware_args);
	if (!empty($ware_data)) {
		?>
		<table id="wareAddress" class="">
			<thead>
				<tr>
					<th>No.</th>
					<th>Shipper Name</th>
					<th>Street</th>
					<th>City, State, Country</th>
					<th>Postal Code</th>
					<th>Action</th>
				</tr>
			</thead>
			<div class="smt-overlay">
				<div class="overlay-content">
					<img class="loaders_img" src="<?php echo plugin_dir_url(__DIR__) . '../assets/img/loading.gif'; ?>">
				</div>
			</div>
			<tbody>
				<?php
				$i = 1;
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
					<tr>
						<td>
							<?php echo $i; ?>
						</td>
						<td>
							<?php echo $sm_shipper_from; ?>
						</td>
						<td>
							<?php echo $sm_street; ?>
						</td>
						<td>
							<?php echo $sm_city . ' , ' . $sm_state . ' , ' . $sm_country; ?>
						</td>
						<td>
							<?php echo $sm_postalCode; ?>
						</td>
						<td>
							<form action="" class="ware-house-form" method="post">
								<input type="hidden" name="sm_warehouse_id" value="<?php echo $ware_post_id; ?>" />
								<div class="">
									<input type="submit" name="sm_delete_warehouse" class="ware-delete-btn" value="Delete" />
								</div>
							</form>
						</td>
					</tr>
					<?php
					$i++;
				}
				?>
			</tbody>
		</table>
	<?php } ?>
</div>