<?php
$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
$manifest_shipment = array();

if ($smart_shipping_setting) {
	$api_key = $smart_shipping_setting['api_key'];
	$args = array(
		'method' => 'GET',
		'timeout' => 300,
		'user-agent' => $_SERVER['HTTP_USER_AGENT'],
		'headers' => array('apikey' => $api_key)
	);

	$EODM_shipments_api_Url = smart_shipping_api_url . "api/carrierapi/GetAllPendingEODMShipments";
	$EODM_shipments_data = wp_remote_get($EODM_shipments_api_Url, $args);
	$EODM_shipments_response = wp_remote_retrieve_body($EODM_shipments_data);
	$EODM_shipments_all_data = json_decode($EODM_shipments_response);
	$manifest_shipment = $EODM_shipments_all_data->Shipments;
}

?>
<div class="manifest_files">
	<div class='notice notice-success'>
		<p>Manifest file generated successfully.</p><a class="download_manifest" href="">Download Manifest file</a>
	</div>
</div>
<div class="manifest_error_main">
	<div class='notice notice-error'>
		<p class="manifest_error"></p>
	</div>
</div>
</br>
<div class="manifest-widget">
	<div class="manifest-notes">
		<p><b>Note: Canada Post shipments must be accompanied by printed End of Day Manifest.</b></br>
			Select shipments from below listing. And then click 'Run End Of Day Manifest Now' button.</p>
	</div>
	<div class="button-eodm">
		<button class="button-primary custom" type="button" id="btnExecuteEodm">Run End Of Day Manifest Now</button>
	</div>
</div>
</br>
</br>
<table id="table_id" class="display">
	<thead>
		<tr>
			<th><input type="checkbox" id="select_all">Select all</th>
			<th>No.</th>
			<th>Date</th>
			<th>EODM Type</th>
			<th>Customer Name</th>
			<th>SMARTT BL#</th>
			<th>PONumber</th>
			<th>Ref Number</th>
			<th>TRK#</th>
		</tr>
	</thead>
	<div class="smt-overlay">
		<div class="overlay-content">
			<img class="loaders_img" src="<?PHP echo plugin_dir_url(__DIR__) . '../assets/img/loading.gif'; ?>" />
		</div>
	</div>
	<tbody>
		<?php
		$i = 1;
		foreach ($manifest_shipment as $manifest_shipment_val) {
			?>
			<tr>
				<td><input type="checkbox" class="shipper_id" id="shipper_id" name="shipper_id"
						value="<?php echo $manifest_shipment_val->ShipmentID; ?>" /></td>
				<td>
					<?php echo $i; ?>
				</td>
				<td>
					<?php echo $manifest_shipment_val->AddedDateStr; ?>
				</td>
				<td>
					<?php echo $manifest_shipment_val->TypeofEODM; ?>
				</td>
				<td>
					<?php echo $manifest_shipment_val->CustomerName; ?>
				</td>
				<td>
					<?php echo $manifest_shipment_val->BLNumber; ?>
				</td>
				<td>
					<?php echo $manifest_shipment_val->PONumber; ?>
				</td>
				<td>
					<?php echo $manifest_shipment_val->RefNumber; ?>
				</td>
				<td>
					<?php echo $manifest_shipment_val->BookingNo; ?>
				</td>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
</table>