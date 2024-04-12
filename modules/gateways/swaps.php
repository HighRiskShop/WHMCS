<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function swaps_MetaData()
{
    return array(
        'DisplayName' => 'swaps',
        'DisableLocalCreditCardInput' => true,
    );
}

function swaps_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'swaps',
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

function swaps_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/swaps.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_swapsapp_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;
	$hrs_swapsapp_final_total = $amount;
				
$hrs_swapsapp_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_swapsapp_wallet_decbody = json_decode($hrs_swapsapp_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_swapsapp_wallet_decbody && isset($hrs_swapsapp_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_swapsapp_gen_addressIn = $hrs_swapsapp_wallet_decbody['address_in'];
		$hrs_swapsapp_gen_callback = $hrs_swapsapp_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_swapsapp_gen_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://api.highriskshop.com/control/process-payment.php?address=' . $hrs_swapsapp_gen_addressIn . '&amount=' . $hrs_swapsapp_final_total . '&provider=swaps&email=' . urlencode($email) . '&currency=' . $hrs_swapsapp_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function swaps_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'swaps gateway activated successfully.');
}

function swaps_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'swaps gateway deactivated successfully.');
}

function swaps_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function swaps_output($vars)
{
    // Output additional information if needed
}

function swaps_error($vars)
{
    // Handle errors if needed
}
