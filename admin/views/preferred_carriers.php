<div class="container ware-wrapper">
	<h3 class="ware-house-title"> Select Preferred Carriers </h3>
	<div style="margin: 5px 0px">
		<span style="font-size: 11px;"> ( Note: All selected carriers will appear on cart,checkout page. If not selected
			then all carriers will appear on cart,checkout page. ) </span>
	</div>
	<button type="button" class="save_preferred_carrier button-primary"> Save Changes </button>
	<div class="success-msg"> </div>
	<div class="error-msg"> </div>
	<div class="smt-overlay">
		<div class="overlay-content">
			<img class="loaders_img" src="<?php echo plugin_dir_url(__DIR__) . '../assets/img/loading.gif'; ?>" />
		</div>
	</div>
	<div class="fl-wrapper preferred-carriers-wrapper">
		<?php
		$smart_shipping_setting = maybe_unserialize(get_option('woocommerce_smartshipping_settings'));
		$preferred_carriers = $singleArray = $groupList = array();
		$th = 1;

		if ($smart_shipping_setting) {
			$api_key = $smart_shipping_setting['api_key'];

			$preferred_carrier_api = array(
				'method' => 'GET',
				'timeout' => 300,
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'headers' => array('apikey' => $api_key)
			);
			$preferredCarrierApiUrl = smart_shipping_api_url . "api/carrierapi/GetAllCarrierServiceOptions";

			$preferred_carrier_request = wp_remote_get($preferredCarrierApiUrl, $preferred_carrier_api);
			$preferred_carrier_response = wp_remote_retrieve_body($preferred_carrier_request);

			$preferred_carrier_data = json_decode($preferred_carrier_response);
			$preferred_carrier_success = $preferred_carrier_data->Success;
			$preferred_carriers = $preferred_carrier_data->Data;

			$groupList = _group_by($preferred_carriers, 'CarrierName');
		}

		foreach ($groupList as $key => $value) {
			if (count($groupList[$key]) < 2) {
				array_push($singleArray, $groupList[$key]);
				continue;
			}
			?>
			<div class="carrier-box">
				<div class="title-box">
					<div class="carrier-heading"><label>
							<?php echo $key; ?>
						</label></div>
					<div class="select-link-box">
						<button class="select_all"> Select All </button>
						<button class="deselect_all"> Deselect All </button>
					</div>
				</div>
				<?php
				foreach ($groupList[$key] as $childArray) {
					$checkbox = "<input type='checkbox' class='carrier-checkbox'>";
					if ($childArray->IsSelected == true) {
						$checkbox = "<input type='checkbox' class='carrier-checkbox' checked='checked'>";
					}
					?>
					<div class="carrier-row">
						<input type="hidden" class="ServiceProductName"
							value="<?php echo $childArray->ServiceProductName; ?>" />
						<input type="hidden" class="ServiceProductKey" value="<?php echo $childArray->ServiceProductKey; ?>" />
						<input type="hidden" class="CarrierName" value="<?php echo $childArray->CarrierName; ?>" />
						<input type="hidden" class="CarrierId" value="<?php echo $childArray->CarrierId; ?>" />
						<div class="fs-11">
							<?php echo $checkbox; ?>
							<?php echo $childArray->CarrierName; ?>
							<span class="fs-10"> (
								<?php echo $childArray->ServiceProductName; ?> )
							</span>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<div class="carrier-box">
			<div class="title-box">
				<div class="carrier-heading">
					<label> Single Service Carriers ( Mostly LTL Truck Type ) </label>
				</div>
				<div class="select-link-box">
					<button class="select_all"> Select All </button>
					<button class="deselect_all"> Deselect All </button>
				</div>
			</div>
			<?php
			foreach ($singleArray as $childSingleArray) {
				foreach ($childSingleArray as $childArray) {
					$checkbox = "<input type='checkbox' class='carrier-checkbox' />";
					if ($childArray->IsSelected == true) {
						$checkbox = "<input type='checkbox' class='carrier-checkbox' checked='checked' />";
					}
					?>
					<div class="carrier-row">
						<input type="hidden" class="ServiceProductName"
							value="<?php echo $childArray->ServiceProductName; ?>" />
						<input type="hidden" class="ServiceProductKey" value="<?php echo $childArray->ServiceProductKey; ?>" />
						<input type="hidden" class="CarrierName" value="<?php echo $childArray->CarrierName; ?>" />
						<input type="hidden" class="CarrierId" value="<?php echo $childArray->CarrierId; ?>" />
						<div class="fs-11">
							<?php echo $checkbox; ?>
							<?php echo $childArray->CarrierName; ?>
							<span class="fs-10"> (
								<?php echo $childArray->ServiceProductName; ?> )
							</span>
						</div>
					</div>
					<?php
				}
			}
			?>
		</div>
		<?php
		function _group_by($array, $key)
		{
			$result = array();
			foreach ($array as $val) {
				if (array_key_exists($key, (array) $val)) {
					$result[$val->$key][] = $val;
				} else {
					$result[""][] = $val;
				}
			}
			return $result;
		}
		?>
	</div>
</div>