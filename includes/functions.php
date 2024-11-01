<?php
add_filter('manage_edit-shop_order_columns', 'sts_add_order_admin_list_column');
function sts_add_order_admin_list_column($columns)
{
	$columns['Actions'] = 'Actions';
	return $columns;
}

add_action('manage_shop_order_posts_custom_column', 'sts_add_new_order_admin_list_column_content');
function sts_add_new_order_admin_list_column_content($column)
{
	global $post;
	if ('Actions' === $column) {
		$post_id = $post->ID;
		$post_link = get_edit_post_link($post_id);
		$label = get_post_meta($post_id, 'Bol_Path', true);

		if ($label != '') {
			echo "<a href='$label'>Download Label</a>";
		} else {
			echo "<a href='$post_link'>Ship Order</a>";
		}
	}
}

add_action('admin_footer', 'sts_footer_scripts');
function sts_footer_scripts()
{
	?>
	<div id="cancelSmarttShipmentModal" class="modal">
		<!-- Modal content -->
		<div class="modal-content">
			<div>
				<span class="close" id="close">&times;</span>
				<p class="enter-email">Cancellation Reason</p>
				<p>* Please provide reason for cancelling:(up to 50 characters)</p>
				<textarea name="smt_cancellation_reason" class="emails" id="smt_cancellation_reason" rows="5"></textarea>

				<input type="hidden" name="smt_post_id" id="smt_post_id" value="<?php echo $post_id; ?>">
				<p name="cancellation-error" id="cancellation_error"> </p>
			</div>
			<div class="send_emails_btn">
				<input type="button" class="cancelSmtShipmentBtn button-primary" id="cancelSmtShipmentBtn" name="cancelSmtShipmentBtn" value="Submit">
			</div>
		</div>
	</div>
<?php
}

function one_usd_to_one_cad()
{
	$req_url = 'https://api.exchangerate-api.com/v4/latest/USD';
	$response_json = file_get_contents($req_url);
	$response_object = json_decode($response_json);
	$CAD_price = round(($response_object->rates->CAD), 2);

	return $CAD_price;
}

function one_csd_to_one_Usd()
{
	$req_url = 'https://api.exchangerate-api.com/v4/latest/CAD';
	$response_json = file_get_contents($req_url);
	$response_object = json_decode($response_json);
	$USD_price = number_format($response_object->rates->USD, 2);

	return $USD_price;
}