<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function particle_MetaData()
{
    return array(
        'DisplayName' => 'particle',
        'DisableLocalCreditCardInput' => true,
    );
}

function particle_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'particle',
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

function particle_link($params)
{
    $walletAddress = $params['wallet_address'];
    $amount = $params['amount'];
    $invoiceId = $params['invoiceid'];
	$email = $params['clientdetails']['email'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $redirectUrl = $systemUrl . '/modules/gateways/callback/particle.php';
	$invoiceLink = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
	$hrs_particlenetwork_currency = $params['currency'];
	$callback_URL = $redirectUrl . '?invoice_id=' . $invoiceId;
	$hrs_particlenetwork_final_total = $amount;
				
$hrs_particlenetwork_gen_wallet = file_get_contents('https://api.highriskshop.com/control/wallet.php?address=' . $walletAddress .'&callback=' . urlencode($callback_URL));


	$hrs_particlenetwork_wallet_decbody = json_decode($hrs_particlenetwork_gen_wallet, true);

 // Check if decoding was successful
    if ($hrs_particlenetwork_wallet_decbody && isset($hrs_particlenetwork_wallet_decbody['address_in'])) {
        // Store the address_in as a variable
        $hrs_particlenetwork_gen_addressIn = $hrs_particlenetwork_wallet_decbody['address_in'];
        $hrs_particlenetwork_gen_polygon_addressIn = $hrs_particlenetwork_wallet_decbody['polygon_address_in'];
		$hrs_particlenetwork_gen_callback = $hrs_particlenetwork_wallet_decbody['callback_url'];
		
		
		 // Update the invoice description to include address_in
            $invoiceDescription = "Payment reference number: $hrs_particlenetwork_gen_polygon_addressIn";

            // Update the invoice with the new description
            $invoice = localAPI("GetInvoice", array('invoiceid' => $invoiceId), null);
            $invoice['notes'] = $invoiceDescription;
            localAPI("UpdateInvoice", $invoice);

		
		
    } else {
return "Error: Payment could not be processed, please try again (wallet address error)";
    }
	
	
        $paymentUrl = 'https://pay.highriskshop.com/process-payment.php?address=' . $hrs_particlenetwork_gen_addressIn . '&amount=' . $hrs_particlenetwork_final_total . '&provider=particle&email=' . urlencode($email) . '&currency=' . $hrs_particlenetwork_currency;

        // Properly encode attributes for HTML output
        return '<a href="' . $paymentUrl . '" class="btn btn-primary" rel="noreferrer">' . $params['langpaynow'] . '</a>';
}

function particle_activate()
{
    // You can customize activation logic if needed
    return array('status' => 'success', 'description' => 'particle gateway activated successfully.');
}

function particle_deactivate()
{
    // You can customize deactivation logic if needed
    return array('status' => 'success', 'description' => 'particle gateway deactivated successfully.');
}

function particle_upgrade($vars)
{
    // You can customize upgrade logic if needed
}

function particle_output($vars)
{
    // Output additional information if needed
}

function particle_error($vars)
{
    // Handle errors if needed
}
