<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function rampnetwork_MetaData()
{
    return array(
        'DisplayName' => 'rampnetwork',
        'DisableLocalCreditCardInput' => true,
    );
}

function rampnetwork_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'rampnetwork',
        ),
        'description' => array(
            'FriendlyName' => 'Description',
            'Type' => 'textarea',
            'Rows' => '3',
            'Cols' => '25',
            'Default' => 'Pay using Credit/debit card (including MasterCard, Visa, and Apple Pay).',
            'Description' => 'This controls the description which the user sees during checkout.',
        ),
        'wallet_address' => array(
            'FriendlyName' => 'USDT Polygon Wallet Address',
            'Type' => 'text',
            'Description' => 'Enter your USDT Polygon Wallet address.',
        ),
    );
}

function rampnetwork_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/rampnetwork.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_rampnetwork_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;

if ($hrs_rampnetwork_currency === 'USD') {
        $hrs_rampnetwork_final_total = $amount;
		} else {
		
$hrs_rampnetwork_response = file_get_contents('https://api.highriskshop.com/control/convert.php?value=' . $amount . '&from=' . strtolower($hrs_rampnetwork_currency));


$hrs_rampnetwork_conversion_resp = json_decode($hrs_rampnetwork_response, true);

if ($hrs_rampnetwork_conversion_resp && isset($hrs_rampnetwork_conversion_resp['value_coin'])) {
    // Escape output
    $hrs_rampnetwork_final_total	= $hrs_rampnetwork_conversion_resp['value_coin'];      
} else {
	return "Error: Payment could not be processed, please try again (unsupported store currency)";
}	
		}
		
		
		
		
$hrs_rampnetwork_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_rampnetwork_wallet_decbody = json_decode($hrs_rampnetwork_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_rampnetwork_wallet_decbody && isset($hrs_rampnetwork_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_rampnetwork_gen_addressIn = $hrs_rampnetwork_wallet_decbody['address_in'];
        $hrs_rampnetwork_gen_polygon_addressIn = $hrs_rampnetwork_wallet_decbody['polygon_address_in'];
		$hrs_rampnetwork_gen_callback = $hrs_rampnetwork_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_rampnetwork_gen_polygon_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://pay.highriskshop.com/process-payment.php?address=' . $hrs_rampnetwork_gen_addressIn . '&amount=' . $hrs_rampnetwork_final_total . '&provider=rampnetwork&email=' . urlencode($email) . '&currency=' . $hrs_rampnetwork_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function rampnetwork_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'rampnetwork gateway activated successfully.');
}

function rampnetwork_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'rampnetwork gateway deactivated successfully.');
}

function rampnetwork_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function rampnetwork_output($vars)
{
    // Output additional information if needed
}

function rampnetwork_error($vars)
{
    // Handle errors if needed
}
