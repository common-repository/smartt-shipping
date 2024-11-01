$ = jQuery;
jQuery(document).ready( function ($) {
    jQuery('#table_id').DataTable();

    jQuery('#select_all').on('click', function() {
        if(this.checked) {
            jQuery('.shipper_id').each( function() {
                this.checked = true;
            });
        } else {
             jQuery('.shipper_id').each( function() {
                this.checked = false;
            });
        }
    });
    // trigger on refresh sandbox API
    jQuery('.smart_shipping_refresh_info_link').click(function(ev) {
        ev.preventDefault();

        if ($('#woocommerce_smartshipping_enabled').is(':checked')) {
            var shipping_enabled = 'yes';
        } else {
            var shipping_enabled = 'no';
        }
        var default_mode = $('#woocommerce_smartshipping_Default_mode').val();
        var api_key = $('#woocommerce_smartshipping_api_key').val();

        jQuery.ajax({
            type: 'POST',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sync_info_from_smartt_shipping', // ajax-functions.php
                nonce: sts_admin_ajax.nonce,
                'api_key': api_key,
                'default_mode': default_mode,
                'shipping_enabled': shipping_enabled
            },
            beforeSend: function() {
                $('#sync-loader').show();
            },
            success: function(response) {
                $('#sync-loader').hide();
                if (response.success === true) {
                    alert('Package, Product, Shipper Info successfully sync from SMARTT Shipping account.');
                } else {
                    alert('Somthing went wrong');
                }
                location.reload();
            }
        });
    });

    jQuery('.carrier-box .select_all').click(function() {
        jQuery(this).parents('.carrier-box').find('.carrier-checkbox').prop('checked', true);
    });

    jQuery('.carrier-box .deselect_all').click(function() {
        jQuery(this).parents('.carrier-box').find('.carrier-checkbox').prop('checked', false);
    });
    //trigger on save preferred carriers 
    jQuery('.save_preferred_carrier').click(function() {
        jQuery('.smt-overlay').show();
        var carrierJson = [];
        jQuery('.preferred-carriers-wrapper .carrier-row').each(function() {

            var ServiceProductName = jQuery(this).find('.ServiceProductName').val();
            var ServiceProductKey = jQuery(this).find('.ServiceProductKey').val();
            var CarrierName = jQuery(this).find('.CarrierName').val();
            var CarrierId = jQuery(this).find('.CarrierId').val();
            var IsSelected = false;

            if (jQuery(this).find('.carrier-checkbox').prop('checked') == true) {
                IsSelected = true;
                var carrierObj = {
                    'ServiceProductName': ServiceProductName,
                    'ServiceProductKey': ServiceProductKey,
                    'CarrierName': CarrierName,
                    'CarrierId': CarrierId,
                    'IsSelected': IsSelected
                }
                carrierJson.push(carrierObj);
            }
        });

        jQuery.ajax({
            type: 'POST',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_save_preferred_carriers', // ajax-functions.php
                nonce: sts_admin_ajax.nonce,
                'preferred_carriers': carrierJson
            },
            success: function(data) {
                if (data != '') {
                    var response = JSON.parse(data);
                    if (response.status == 'success') {
                        jQuery('.success-msg').text('Preferred carriers info successfully saved.').show();
                    } else {
                        jQuery('.error-msg').text('Something went wrong, Please try after some time.').show();
                    }
                }
                setTimeout(function() {
                    jQuery('.error-msg,.success-msg').hide();
                }, 10000);
                jQuery('.smt-overlay').hide();
            }
        });
    });

    jQuery('.smt-shipexportimport').hide();
    jQuery('#cform_chk_below_800').click(function() {
        jQuery('#smt-article-msg').show();
        jQuery('#smt-clear-ship').show();
        jQuery('.smt-shipexportimport').hide();
        jQuery('#smt-clearshippment').hide();
        jQuery('.smt-allitemsdatatable').hide();
    });

    jQuery('#cform_chk_above_800').click(function() {
        jQuery('#smt-article-msg').hide();
        jQuery('#smt-clear-ship').hide();
        jQuery('.smt-shipexportimport').show();
        jQuery('#smt-clearshippment').show();
        jQuery('.smt-allitemsdatatable').show();
    });

    jQuery('#smt_shipbrokertrue').click(function() {
        jQuery('.smt-shipexportimport').show();
        jQuery('#smt-clearshippment').show();
        jQuery('.smt-allitemsdatatable').show();
    });

    jQuery('#smt_shipbrokerfalse').click(function() {
        jQuery('#smt-clearshippment').hide();
        jQuery('.smt-shipexportimport').show();
        jQuery('.smt-allitemsdatatable').show();
    });
    // trigger on get all carrier rates
    jQuery('#smt_get_rates').click(function() {
        debugger;
        var len = jQuery('.smt-required-fields').length;
        var j = 1;
        var product_data = [];
        while (len >= j) {
            var length = jQuery('#length' + j).val();
            var width = jQuery('#width' + j).val();
            var height = jQuery('#height' + j).val();
            var weight = jQuery('#weight' + j).val();
            var quantity = jQuery('#quantity' + j).val();
            var get_packages = jQuery('#smt_get_packages' + j).val();
            var shipstation_product = jQuery('#smt_shipstation_product' + j).val();
            var dangerous_goods = jQuery('#dangerous_goods' + j).is(':checked');
            var non_stackable = jQuery('#non_stackable' + j).is(':checked');
            var row = {
                'length': length,
                'width': width,
                'height': height,
                'weight': weight,
                'quantity': quantity,
                'get_packages': get_packages,
                'dangerous_goods': dangerous_goods,
                'non_stackable': non_stackable,
                'shipstation_product': shipstation_product
            };
            product_data.push(row);
            j++;
        }

        var residential_delivery = jQuery('#smt_residential_delivery').is(':checked');
        var power_tailgate_delivery = jQuery('#smt_power_tailgate_delivery').is(':checked');
        var is_drop_off = jQuery('#smt_is_drop_off').is(':checked');
        var delivery_signature_required = jQuery('#smt_delivery_signature_required').is(':checked');
        var order_id = jQuery('#order_id').val();
        var shipping_date = jQuery('#smt_shipping_date').val();
        var shipping_start_time = jQuery('#smt_shipping_start_time').val();
        var shipping_end_time = jQuery('#smt_shipping_end_time').val();
        var warehouse_id = jQuery('#warehouse_id').val();
        var is_shipping_country = jQuery('#smt_is_shipping_country').val();
        if (shipping_date == '') {
            alert('Please Choose Shipment Date First');
            return false;
        }
        var insurance = jQuery('#smt_insurance').val();
        var ship_value = jQuery('input[name="ship_value_below_800"]:checked').val();
        var shipbroker = jQuery('input[name="shipbroker"]:checked').val();
        var importerrecord = jQuery('#smt_importer_record').val();
        var is_clearing_shipment = jQuery('#smt_is_clearing_shipment').val();
        var importexporttype = jQuery('#smt_import_export_type').val();
        var is_rowCount = jQuery('#smt_is_tbl_customers #is_main_all_data').closest('tr').length;
        var is_j = 1;
        var is_item_data = [];
        while ( is_rowCount >= is_j ) {
            var is_description = jQuery('#is_description' + is_j).val();
            var is_quantity = jQuery('#is_quantity' + is_j).val();
            var is_weight = jQuery('#is_weight' + is_j).val();
            var is_price = jQuery('#is_price' + is_j).val();
            var is_tarrif_code = jQuery('#is_tarrif_code' + is_j).val();
            var is_mcountry = jQuery('#is_mcountry' + is_j).val();
            var is_mstate = jQuery('#is_mstate' + is_j).val();
            var is_currency = jQuery('#is_currency' + is_j).val();
            var get_package_names = jQuery('#smt_get_packages' + is_j+ ' option:selected').text();
            var is_row_data = {
                'is_description': is_description,
                'is_quantity': is_quantity,
                'is_price': is_price,
                'is_weight': is_weight,
                'is_mcountry': is_mcountry,
                'is_mstate': is_mstate,
                'is_currency': is_currency,
                'get_package_names': get_package_names.trim(),
                'is_tarrif_code': is_tarrif_code

            };
            is_item_data.push(is_row_data);
            is_j++;
        }

        jQuery('.smt-overlay').show();
        jQuery.ajax({
            type: 'POST',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_get_disptach_rates', // ajax-functions.php
                nonce: sts_admin_ajax.nonce,
                all_product_data: product_data,
                order_id: order_id,
                insurance: insurance,
                warehouse_id: warehouse_id,
                shipping_date: shipping_date,
                shipping_start_time: shipping_start_time,
                shipping_end_time: shipping_end_time,
                residential_delivery: residential_delivery,
                power_tailgate_delivery: power_tailgate_delivery,
                is_drop_off: is_drop_off,
                is_clearing_shipment: is_clearing_shipment,
                importerrecord: importerrecord,
                importexporttype: importexporttype,
                is_item_data: is_item_data,
                ship_value: ship_value,
                shipbroker: shipbroker,
                is_shipping_country: is_shipping_country,
                delivery_signature_required: delivery_signature_required
            },
            beforeSend: function(){
                $("#smt_rate_table, .smt-shipping-error").hide();
            },
            success: function(response) {
                if (response.success == true) {
                    smt_create_label(order_id,response.data);
                    return;
                }
                jQuery('.smt-shipping-error').hide();
                jQuery('#smt_rate_table').html(response);
                jQuery('#smt_rate_table').show();
                jQuery('.smt-overlay').hide();
            }
        });
    });
    
    jQuery('#smt_all_dynanic_fields input[type=number]').keyup(function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('#smt_all_dynanic_fields input[type=text]').keyup(function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('#smt_all_dynanic_fields select').change(function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('.smt-usa-data select').change(function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('.smt-usa-data input[type=radio]').change(function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('div').delegate('#smt_all_dynanic_fields input[type=text]', 'keyup', function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('div').delegate('.smt-usa-data input[type=text]', 'keyup', function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('.smt_custom_shipping_fields input[type="date"]').change(function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('.smt_custom_shipping_fields input[type="checkbox"]').click(function() {
        jQuery('#smt_rate_table').hide();
    });

    jQuery('div').delegate('#smt_all_dynanic_fields select', 'change', function() {
        jQuery('#smt_rate_table').hide();
    });
    // trigger on chnage ship station products
    jQuery(document).on('change', '.smt_shipstation_product', function() {
        debugger;
        var selected_id = jQuery(this).attr('data');
        var text = jQuery('#smt_shipstation_product' + selected_id + ' option:selected').html();
        jQuery('.smt-overlay').show();

        jQuery.ajax({
            type: 'POST',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_get_dangerous_fields',
                nonce: sts_admin_ajax.nonce,
                dangerous_data: text,
                selected_id: selected_id
            },
            success: function(data) {
                var datas = jQuery.trim(data);
                if (datas == 1) {
                    jQuery('#dangerous_goods' + selected_id).prop('checked', true);
                } else {
                    jQuery('#dangerous_goods' + selected_id).prop('checked', false);
                }
                jQuery('.smt-overlay').hide();
            }
        });
    });
    // trigger on change get packages
    jQuery(document).on('change', '.smt_get_packages', function() {
        debugger;
        var selected_id = jQuery(this).attr('data');
        var val = jQuery(this).val();
        var text = jQuery('#smt_get_packages' + selected_id + ' option:selected').html();
        var smt_length = jQuery('#length' + selected_id).val();
        var smt_width = jQuery('#width' + selected_id).val();
        var smt_height = jQuery('#height' + selected_id).val();
        var smt_weight = jQuery('#weight' + selected_id).val();

        jQuery('.smt-overlay').show();

        jQuery.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_package_value', // ajax-functions.php
                nonce: sts_admin_ajax.nonce,
                smt_package_data: text,
                smt_length: smt_length,
                smt_width: smt_width,
                smt_height: smt_height,
                smt_weight: smt_weight
            },
            success: function(datas) {
                var resp = datas.responses;
                var data = datas.data;
                if (resp == 'success') {
                    var width = data.package_width;
                    var length = data.package_length;
                    var height = data.package_height;
                    var weight = data.package_weight;
                    if (weight == null) {
                        weight = 1;
                    }

                    jQuery('#length' + selected_id).val(length);
                    jQuery('#width' + selected_id).val(width);
                    jQuery('#height' + selected_id).val(height);
                    jQuery('#weight' + selected_id).val(weight);

                    jQuery('#length' + selected_id).prop('disabled', true);
                    jQuery('#width' + selected_id).prop('disabled', true);
                    jQuery('#height' + selected_id).prop('disabled', true);
                    jQuery('#weight' + selected_id).prop('disabled', true);

                } else {
                    jQuery('#length' + selected_id).val(smt_length);
                    jQuery('#width' + selected_id).val(smt_width);
                    jQuery('#height' + selected_id).val(smt_height);
                    jQuery('#weight' + selected_id).val(smt_weight);
                    jQuery('#length' + selected_id).prop('disabled', false);
                    jQuery('#width' + selected_id).prop('disabled', false);
                    jQuery('#height' + selected_id).prop('disabled', false);
                    jQuery('#weight' + selected_id).prop('disabled', false);
                }
                jQuery('.smt-overlay').hide();
            }
        });
    });
    // trigger on get product all packages
    jQuery('.smt_product_all_packages').change(function() {
        debugger;
        var text = jQuery('.smt_product_all_packages option:selected').html();
        var smt_length = jQuery('#product_length').val();
        var smt_width = jQuery('#product_width').val();
        var smt_height = jQuery('#product_height').val();
        var smt_weight = jQuery('#_weight').val();
        var smtLoaderUrl = sts_admin_ajax.smtLoaderUrl;
        
        jQuery('#shipping_product_data').append("<div class='smt-overlay'><div class='overlay-content'><img class='loaders_img' src='" + smtLoaderUrl + "'></div></div>");
        jQuery('.smt-overlay').show();
        jQuery.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_package_value', // ajax-functions.php
                nonce: sts_admin_ajax.nonce,
                smt_package_data: text,
                smt_length: smt_length,
                smt_width: smt_width,
                smt_height: smt_height,
                smt_weight: smt_weight
            },
            success: function(datas) {
                var resp = datas.responses;
                var data = datas.data;

                if (resp == 'success') {
                    var width = data.package_width;
                    var length = data.package_length;
                    var height = data.package_height;
                    var weight = data.package_weight;
                    if (weight == null) {
                        weight = 1;
                    }
                    jQuery('#product_length').val(length);
                    jQuery('#product_width').val(width);
                    jQuery('#product_height').val(height);
                    jQuery('#_weight').val(weight);

                } else {
                    jQuery('#product_length').val(smt_length);
                    jQuery('#product_width').val(smt_width);
                    jQuery('#product_height').val(smt_height);
                    jQuery('#_weight').val(smt_weight);
                }
                jQuery('.smt-overlay').hide();
            }
        });
    });

    jQuery('.smt_custom_product').change(function() {
        var text = jQuery('.smt_custom_product option:selected').val();
        var res = text.split('_');
        var isdanger = res[1];

        if (isdanger == 1) {
            jQuery('.smt_dangerous_product').prop('checked', true);
        } else {
            jQuery('.smt_dangerous_product').prop('checked', false);
        }
    });

    jQuery('#btnExecuteEodm').click(function() {
        var eodm_shipment_id = [];
        jQuery('input:checkbox[name="shipper_id"]:checked').each(function() {
            eodm_shipment_id.push(jQuery(this).val());
        });

        var shipment_id = eodm_shipment_id.join(', ');
        if (shipment_id == '') {
            alert('please select at least one shipment');
            return false;
        }

        jQuery('.smt-overlay').show();
        jQuery.ajax({
            type: 'post',
            dataType: 'JSON',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'get_manifest_data',
                nonce: sts_admin_ajax.nonce,
                shipment_id: shipment_id
            },
            success: function(datas) {
                jQuery('.smt-overlay').hide();
                var resp = datas.responses;
                if (resp == 'success') {
                    var manifest = datas.manifest;
                    jQuery('.manifest_files').show();
                    jQuery('.manifest_error_main').hide();
                    jQuery('.download_manifest').attr('href', manifest);
                } else {
                    var message = datas.Message;
                    jQuery('.manifest_error_main').show();
                    jQuery('.manifest_files').hide();
                    jQuery('.manifest_error').html(message);
                }
            }
        });
    });
    // trigger on cancel shipment from modal
    jQuery('#cancelSmtShipmentBtn').on('click', function() {
        debugger;

        var cancellation_reason = jQuery('#smt_cancellation_reason').val();
        if (cancellation_reason == '') {
            alert('Please insert Valid Reason');
            return false;
        }

        var smt_post_id = jQuery('#smt_post_id').val();
        jQuery.ajax({
            type: 'post',
            url: sts_admin_ajax.ajax_url,
            dataType: 'JSON',
            data: {
                action: 'sts_cancel_shipment', //ajax-functions.php
                nonce: sts_admin_ajax.nonce,
                post_id: smt_post_id,
                cancellation_reason: cancellation_reason
            },
            success: function(datas) {
                jQuery('#cancelSmarttShipmentModal').hide();
                var resp = datas.responses;
                var message = datas.message;

                if (resp == 'success') {
                    alert(message);
                }
                if (resp == 'error') {
                    alert(message);
                }
                window.location.reload();
            }
        });
    });
    // trigger on cancel cancel shipment
    jQuery(document).on('click', '#cancel_shpment', function(e){
        e.preventDefault();
        var post_id = $(this).data('id');
        if (confirm('Are you sure you want to cancel the Shipment ?')) {
            jQuery('#cancelSmarttShipmentModal').css('display', 'block');
            jQuery('#smt_post_id').val(post_id);
            
            jQuery('#close').click(function() {
                jQuery('#cancelSmarttShipmentModal').css('display', 'none');
            });
        }   
    });
    //  trigger on select manufacture country
    jQuery('#_select-mcountry').on('change', function() {
        var selectedCountry = $(this).val();
        var stateSelect = $('#_select-mstate');
        
        jQuery.ajax({
            type: 'POST',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_get_states_by_country',
                country: selectedCountry,
            },
            success: function(states) {
                stateSelect.empty();
                jQuery.each(states, function(code, name) {
                    stateSelect.append('<option value="' + code + '">' + name + '</option>');
                });
            }
        });
    });

    jQuery('#woocommerce_smartshipping_Default_mcountry').on('change', function() {
        var selectedCountry = $(this).val();
        var stateSelect = $('#woocommerce_smartshipping_Default_mstate');
        
        jQuery.ajax({
            type: 'POST',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_get_states_by_country',
                country: selectedCountry,
            },
            success: function(states) {
                stateSelect.empty();
                jQuery.each(states, function(code, name) {
                    stateSelect.append('<option value="' + code + '">' + name + '</option>');
                });
            }
        });
    });

    jQuery('select[name=is_mcountry]').on('change', function(){
        var data_id = $(this).data('id');
        var selectedCountry = $('#is_mcountry' + data_id).val();
        var stateSelect = $('#is_mstate' + data_id);
        if (selectedCountry !== 'CA') {                                          
            stateSelect.html('<option value="">Not required</option>');
            stateSelect.attr('disabled', 'true');
        } else {
            jQuery.ajax({
                type: 'POST',
                url: sts_admin_ajax.ajax_url,
                data: {
                    action: 'sts_get_states_by_country',
                    country: selectedCountry,
                },
                success: function(states) {
                    $('#is_mstate' + data_id).removeAttr('disabled');
                    stateSelect.empty();
                    jQuery.each(states, function(code, name) {
                        stateSelect.append('<option value="' + code + '">' + name + '</option>');
                    });
                }
            });
        }
    });

    // trigger on select warehouse country
    jQuery('select[name=sm_country]').on('change', function() {
        var selectedCountry = $(this).val();
        var stateSelect = $('select[name=sm_state]');
        
        jQuery.ajax({
            type: 'POST',
            url: sts_admin_ajax.ajax_url,
            data: {
                action: 'sts_warehouse_get_states_by_country',
                country: selectedCountry,
            },
            success: function(states) {
                stateSelect.empty();
                jQuery.each(states, function(code, name) {
                    stateSelect.append('<option value="' + name + '">' + name + '</option>');
                });
            }
        });
    });

    jQuery('#add-warehouse-address').on('submit', function(e){
        e.preventDefault();

        var formData = $(this).serialize();
        if (warehouseValidateForm()) {
            jQuery.ajax({
                type: 'POST',
                url: sts_admin_ajax.ajax_url,
                data: {
                    action: 'sts_save_warehouse_address',
                    nonce: sts_admin_ajax.nonce,
                    formData: formData,
                },
                beforeSend: function(){
                    $("#required-error").hide();
                    $('#warehouse_loader').show();
                },
                success: function(response) {
                    $('#warehouse_loader').hide();
                    if (response.success == true) {
                        location.reload();
                    } else {
                        $("#required-error").text(response.data).show();
                    }
                }
            });
        } else {
            $("#required-error").show();
        }
    });
});

function warehouseValidateForm() {
    // Validate required fields
    jQuery("#required-error").hide();
    var fields = ['sm_shipper_from', 'sm_email', 'sm_phone', 'sm_street', 'sm_country', 'sm_state', 'sm_city', 'sm_postalCode'];
    var isValid = true;

    for (var i = 0; i < fields.length; i++) {
        var fieldName = fields[i];
        var fieldValue = jQuery('[name="' + fieldName + '"]').val();

        if (!fieldValue) {
            isValid = false;
            break;
        }
    }

    return isValid;
}

// function for create label
function smt_create_label(order_id, SelectedCarrier) {
    debugger;
    var len = jQuery('.smt-required-fields').length;
    var j = 1;
    var product_data = [];
    while (len >= j) {
        var length = jQuery('#length' + j).val();
        var width = jQuery('#width' + j).val();
        var height = jQuery('#height' + j).val();
        var weight = jQuery('#weight' + j).val();
        var quantity = jQuery('#quantity' + j).val();
        var get_packages = jQuery('#smt_get_packages' + j).val();
        var shipstation_product = jQuery('#smt_shipstation_product' + j).val();
        var dangerous_goods = jQuery('#dangerous_goods' + j).is(':checked');
        var non_stackable = jQuery('#non_stackable' + j).is(':checked');
        var row = {
            'length': length,
            'width': width,
            'height': height,
            'weight': weight,
            'quantity': quantity,
            'get_packages': get_packages,
            'dangerous_goods': dangerous_goods,
            'non_stackable': non_stackable,
            'shipstation_product': shipstation_product
        };
        product_data.push(row);
        j++;
    }

    var residential_delivery = jQuery('#smt_residential_delivery').is(':checked');
    var power_tailgate_delivery = jQuery('#smt_power_tailgate_delivery').is(':checked');
    var delivery_signature_required = jQuery('#smt_delivery_signature_required').is(':checked');
    var is_drop_off = jQuery('#smt_is_drop_off').is(':checked');
    var order_id = jQuery('#order_id').val();
    var shipping_date = jQuery('#smt_shipping_date').val();
    var shipping_start_time = jQuery('#smt_shipping_start_time').val();
    var shipping_end_time = jQuery('#smt_shipping_end_time').val();
    var special_instructions = jQuery('#special_instructions').val();
    var insurance = jQuery('#smt_insurance').val();
    var ship_value = jQuery('input[name="ship_value_below_800"]:checked').val();
    var shipbroker = jQuery('input[name="shipbroker"]:checked').val();
    var importerrecord = jQuery('#smt_importer_record').val();
    var is_clearing_shipment = jQuery('#smt_is_clearing_shipment').val();
    var importexporttype = jQuery('#smt_import_export_type').val();
    var is_shipping_country = jQuery('#smt_is_shipping_country').val();
    var warehouse_id = jQuery('#warehouse_id').val();
    var is_rowCount = jQuery('#smt_is_tbl_customers #is_main_all_data').closest('tr').length;
    var is_j = 1;
    var is_item_data = [];
    while (is_rowCount >= is_j) {
        var is_description = jQuery('#is_description' + is_j).val();
        var is_quantity = jQuery('#is_quantity' + is_j).val();
        var is_weight = jQuery('#is_weight' + is_j).val();
        var is_price = jQuery('#is_price' + is_j).val();
        var is_tarrif_code = jQuery('#is_tarrif_code' + is_j).val();
        var is_mcountry = jQuery('#is_mcountry' + is_j).val();
        var is_mstate = jQuery('#is_mstate' + is_j).val();
        var is_currency = jQuery('#is_currency' + is_j).val();
        var get_package_names = jQuery('#smt_get_packages' + is_j+ ' option:selected').text();
        var is_row_data = {
            'is_description': is_description,
            'is_quantity': is_quantity,
            'is_price': is_price,
            'is_weight': is_weight,
            'is_mcountry': is_mcountry,
            'is_mstate': is_mstate,
            'is_currency': is_currency,
            'get_package_names': get_package_names,
            'is_tarrif_code': is_tarrif_code
        };
        is_item_data.push(is_row_data);
        is_j++;
    }

    jQuery('.smt-overlay').show();
    jQuery.ajax({
        type: 'POST',
        url: sts_admin_ajax.ajax_url,
        dataType: 'JSON',
        data: {
            action: 'sts_create_dispatch',
            nonce: sts_admin_ajax.nonce,
            all_product_data: product_data,
            order_id: order_id,
            insurance: insurance,
            shipping_date: shipping_date,
            shipping_start_time: shipping_start_time,
            shipping_end_time: shipping_end_time,
            SelectedCarrier,
            warehouse_id: warehouse_id,
            residential_delivery: residential_delivery,
            power_tailgate_delivery: power_tailgate_delivery,
            is_drop_off: is_drop_off,
            special_instructions: special_instructions,
            is_clearing_shipment: is_clearing_shipment,
            importerrecord: importerrecord,
            importexporttype: importexporttype,
            is_item_data: is_item_data,
            ship_value: ship_value,
            shipbroker: shipbroker,
            is_shipping_country: is_shipping_country,
            delivery_signature_required: delivery_signature_required
        },
        success: function(data) {
            var resp = data.responses;
            var message = data.message;
            if (resp == 'success') {
                jQuery('.custom-shipping-form').hide();
                jQuery('.smt-usa-data').hide();
                jQuery('#fetchratesdiv').hide();
            }
            jQuery('.smt-shipping-error').show();
            jQuery('.smt-shipping-error').html(message);
            jQuery('#smt_rate_table').hide();
            jQuery('.smt-overlay').hide();
        }
    });
}