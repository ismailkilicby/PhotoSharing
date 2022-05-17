<?php

use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;




use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

use SecurionPay\SecurionPayGateway;
use SecurionPay\Exception\SecurionPayException;
use SecurionPay\Request\CheckoutRequestCharge;
use SecurionPay\Request\CheckoutRequest;
if ($action == 'get_paypal_link' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret'])) {
    require_once('sys/paypal_config.php');
    $type = 'pro';
    $sum = $config['pro_price'];
    $dec = "Upgrade to pro";
    if (!empty($_POST['type']) && $_POST['type'] == 'wallet' && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $sum = Generic::secure($_POST['amount']);
        $type = 'wallet';
        $dec = "Wallet top up";
    }
    $org_amount = $sum;
    if (!empty($config['currency_array']) && in_array($config['paypal_currency'], $config['currency_array']) && $config['paypal_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['paypal_currency']])) {
        $sum= (($sum * $config['exchange'][$config['paypal_currency']]));
    }
    $sum = (int)$sum;
    $inputFields = new InputFields();
    $inputFields->setAllowNote(true)
        ->setNoShipping(1)
        ->setAddressOverride(0);
    $webProfile = new WebProfile();
    $webProfile->setName($dec." ". uniqid())
        ->setInputFields($inputFields);
    try {
        $createdProfile = $webProfile->create($paypal);
        $createdProfileID = json_decode($createdProfile);
        $profileid = $createdProfileID->id;
    } catch(PayPal\Exception\PayPalConnectionException $pce) {
        $data = array(
            'type' => 'ERROR',
            'details' => json_decode($pce->getData())
        );
        return $data;
    }
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');
    $item = new Item();
    $item->setName($dec)->setQuantity(1)->setPrice($sum)->setCurrency($config['paypal_currency']);
    $itemList = new ItemList();
    $itemList->setItems(array(
        $item
    ));
    $details = new Details();
    $details->setSubtotal($sum);
    $amount = new Amount();
    $amount->setCurrency($config['paypal_currency'])->setTotal($sum)->setDetails($details);
    $transaction = new Transaction();
    $transaction->setAmount($amount)->setItemList($itemList)->setDescription($dec)->setInvoiceNumber(uniqid());
    $redirectUrls = new RedirectUrls();
    if ($type == 'pro') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/get_paid&success=1")->setCancelUrl($config['site_url']);
    }
    elseif ($type == 'wallet') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/wallet_top_up&success=1&amount=".$org_amount)->setCancelUrl($config['site_url']);
    }
    $payment = new Payment();
    $payment->setExperienceProfileId($profileid)->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array(
        $transaction
    ));
    try {
        $payment->create($paypal);
    }
    catch (Exception $e) {
        $data = array(
            'status' => 200,
            'message' => json_decode($e->getData())
        );
        if (empty($data['message'])) {
            $data['message'] = json_decode($e->getCode());
        }
        return $data;
    }
    $data = array(
        'status' => 200,
        'url' => $payment->getApprovalLink()
    );





    
    // $payer = new Payer();
    // $payer->setPaymentMethod('paypal');
    // $item = new Item();
    // $item->setName($dec)->setQuantity(1)->setPrice($sum)->setCurrency($config['currency']);
    // $itemList = new ItemList();
    // $itemList->setItems(array(
    //     $item
    // ));
    // $details = new Details();
    // $details->setSubtotal($sum);
    // $amount = new Amount();
    // $amount->setCurrency($config['currency'])->setTotal($sum)->setDetails($details);
    // $transaction = new Transaction();
    // $transaction->setAmount($amount)->setItemList($itemList)->setDescription($dec)->setInvoiceNumber(time());
    // $redirectUrls = new RedirectUrls();
    // if ($type == 'pro') {
    //     $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/get_paid&success=1")->setCancelUrl($config['site_url']);
    // }
    // elseif ($type == 'wallet') {
    //     $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/wallet_top_up&success=1&amount=".$sum)->setCancelUrl($config['site_url']);
    // }
    // $payment = new Payment();
    // $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array(
    //     $transaction
    // ));
    // try {
    //     $payment->create($paypal);
    // }
    // catch (Exception $e) {
    //     $data = array(
    //         'status' => 400,
    //         'message' => json_decode($e->getData())
    //     );
    //     if (empty($data['message'])) {
    //         $data['message'] = json_decode($e->getCode());
    //     }
    // }
    // if (empty($data['message'])) {
    //     $data = array(
    //         'status' => 200,
    //         'url' => $payment->getApprovalLink()
    //     );
    // }
    
}

if(($action == 'paysera_success' || $action == 'paysera_callback') && !empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0){
    $response = WebToPay::checkResponse($_GET, array(
        'projectid'     => $config['paysera_project_id'],
        'sign_password' => $config['paysera_password'],
    ));

    if ($response['type'] !== 'macro') {
        die('Only macro payment callbacks are accepted');
    }
    $amount = Generic::secure($_GET['amount']);
    $wallet = $me['wallet'] + $amount;
    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                  'amount' => $amount,
                                  'type' => 'Advertise',
                                  'time' => time()));
    if (!empty($_COOKIE['redirect_page'])) {
        $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
        $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
        header("Location: " . $redirect_page);
    }
    else{
        header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
    }
    exit();
}
if($action == 'paysera_cancel'){
    header('Location: ' . $config['site_url']);
    exit();
}
if($action == 'get_sms_link' && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0){
    $amount = intval($_POST['amount']);
    $url = '';
    try {
        $self_url = $config['site_url'];
        $payment_url = WebToPay::getPaymentUrl();

        $request = WebToPay::buildRequest(array(
            'projectid'     => $config['paysera_project_id'],
            'sign_password' => $config['paysera_password'],
            'orderid'       => rand(1111,4444),
            'amount'        => $amount,
            'currency'      => $config['currency'],
            'country'       => 'TR',
            'accepturl'     => $self_url.'/aj/go_pro/paysera_success?amount='.$amount,
            'cancelurl'     => $self_url.'/aj/go_pro/paysera_cancel',
            'callbackurl'   => $self_url.'/aj/go_pro/paysera_callback',
            'test'          => ($config['paysera_test_mode'] == 'test') ? 1 : 0,
        ));

        $url = $payment_url . '?data='. $request['data'] . '&sign=' . $request['sign'];
        $data = array(
            'status' => 200,
            'url' => $url
        ); 
    }
    catch (WebToPayException $e) {
        echo $e->getMessage();
    }
}

if ($action == 'get_paid' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID'])) {
    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {
        $update = $user->updateStatic($me['user_id'],array('is_pro' => 1,'verified' => 1));
        $amount = $config['pro_price'];
        $date   = time();

        $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'pro_member',
                                      'date' => $date));

        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'pro_member',
                                      'time' => $date));

        header("Location: " . $config['site_url'] . "/upgraded");
        exit();
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if ($action == 'wallet_top_up' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID']) && !empty($_GET['amount'])) {
    require_once('sys/paypal_config.php');
    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {
        $wallet = $me['wallet'] + $_GET['amount'];
        $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => Generic::secure($_GET['amount']),
                                      'type' => 'Advertise',
                                      'time' => time()));
        if (!empty($_COOKIE['redirect_page'])) {
            $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
            $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
            header("Location: " . $redirect_page);
        }
        else{
            header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
        }
        exit();
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if ($action == 'stripe_payment' && IS_LOGGED && $config['credit_card'] == 'on' && !empty($config['stripe_id']) && !empty($config['stripe_id'])) {
    require_once('sys/import3p/stripe-php-3.20.0/vendor/autoload.php');
    $stripe = array(
      "secret_key"      =>  $config['stripe_secret'],
      "publishable_key" =>  $config['stripe_id']
    );

    \Stripe\Stripe::setApiKey($stripe['secret_key']);
    $token = $_POST['stripeToken'];

    if (!empty($_POST['type']) && $_POST['type'] == 'pro' && !empty($_POST['amount'])) {
        if ($config['pro_price'].'00' == $_POST['amount']) {
            try {
                $customer = \Stripe\Customer::create(array(
                    'source' => $token
                ));
                $charge   = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount' => $config['pro_price'].'00',
                    'currency' => 'usd'
                ));
                if ($charge) {
                    $update = $user->updateStatic($me['user_id'],array('is_pro' => 1,'verified' => 1));
                    $amount = $config['pro_price'];
                    $date   = time();

                    $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'type' => 'pro_member',
                                              'date' => $date));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'pro_member',
                                      'time' => $date));
                    $data = array(
                        'status' => 200,
                        'url' => $config['site_url'] . "/upgraded"
                    );
                }
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'error' => $e->getMessage()
                );
            }
        }
    }
    elseif (!empty($_POST['type']) && $_POST['type'] == 'wallet' && !empty($_POST['amount'])) {
        $amount = Generic::secure($_POST['amount']);
        try {
            $customer = \Stripe\Customer::create(array(
                'source' => $token
            ));
            $charge   = \Stripe\Charge::create(array(
                'customer' => $customer->id,
                'amount' => $_POST['amount'].'00',
                'currency' => 'usd'
            ));
            if ($charge) {
                $wallet = $me['wallet'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'Advertise',
                                      'time' => time()));
                $data = array(
                    'status' => 200,
                    'url' => $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '')
                );
            }
        }
        catch (Exception $e) {
            $data = array(
                'status' => 400,
                'error' => $e->getMessage()
            );
        }
    }

    
    
}

if ($action == 'bank_transfer' && IS_LOGGED) {
    if (!empty($_FILES['image'])) {
        if (!empty($_FILES['image']) && file_exists($_FILES['image']['tmp_name'])) {
            $media = new Media();
            $media->setFile(array(
                'file' => $_FILES['image']['tmp_name'],
                'name' => $_FILES['image']['name'],
                'size' => $_FILES['image']['size'],
                'type' => $_FILES['image']['type'],
                'allowed' => 'jpeg,jpg,png'
            ));

            $upload = $media->uploadFile();

            $description = 'Upgrade to pro';
            $price = $config['pro_price'];
            $mode  = 'pro_member';
            $funding_id  = 0;

            if (!empty($_POST['type']) && $_POST['type'] == 'wallet' && !empty($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0) {
                $description = 'Wallet top up';
                $mode  = 'wallet';
                $price = Generic::secure($_POST['price']);
            }
            if (!empty($_POST['type']) && $_POST['type'] == 'donate' && !empty($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0 && !empty($_POST['fund_id'])) {
                $description = 'Donate to funding ';
                $mode  = 'donate';
                $price = Generic::secure($_POST['price']);
                $funding_id = Generic::secure($_POST['fund_id']);
            }
            if (!empty($upload)) { 
                $image = $upload['filename'];
                $db->insert(T_BANK_TRANSFER,array('user_id' => $me['user_id'],
                                          'receipt_file' => $image,
                                          'description' => $description,
                                          'price' => $price,
                                          'mode' => $mode,
                                          'funding_id' => $funding_id));
                $data['status']  = 200;
                $data['message'] = lang('bank_transfer_request');
            }
        }
    }
    else{
        $data = array(
            'status' => 400,
            'message' => lang('please_fill_fields')
        );
    }
}

if ($action == 'paypal_donate' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret'])) {

    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0 && !empty($_POST['fund_id']) && is_numeric($_POST['fund_id']) && $_POST['fund_id'] > 0) {

        $user = new User();
        $fund_id = Generic::secure($_POST['fund_id']);

        $fund = $user->GetFundingById($fund_id);
        if (!empty($fund)) {
            $sum = Generic::secure($_POST['amount']);
            $type = 'wallet';
            $dec = "donate";


            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            $item = new Item();
            $item->setName($dec)->setQuantity(1)->setPrice($sum)->setCurrency($config['currency']);
            $itemList = new ItemList();
            $itemList->setItems(array(
                $item
            ));
            $details = new Details();
            $details->setSubtotal($sum);
            $amount = new Amount();
            $amount->setCurrency($config['currency'])->setTotal($sum)->setDetails($details);
            $transaction = new Transaction();
            $transaction->setAmount($amount)->setItemList($itemList)->setDescription($dec)->setInvoiceNumber(time());
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/donate_to_user&amount=".$sum."&fund_id=".$fund_id)->setCancelUrl($config['site_url']);
            $payment = new Payment();
            $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array(
                $transaction
            ));
            try {
                $payment->create($paypal);
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'message' => json_decode($e->getData())
                );
                if (empty($data['message'])) {
                    $data['message'] = json_decode($e->getCode());
                }
            }

            if (empty($data['message'])) {
                $data = array(
                    'status' => 200,
                    'url' => $payment->getApprovalLink()
                );
            }
        }
        else{
            $data = array(
                'status' => 400,
                'message' => lang('fund_not_found')
            ); 
        }
    }
    else{
        $data = array(
            'status' => 400,
            'message' => lang('please_fill_fields')
        ); 
    }
}

if ($action == 'donate_to_user' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && !empty($_GET['paymentId']) && !empty($_GET['PayerID']) && !empty($_GET['amount']) && !empty($_GET['fund_id'])) {

    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {

        $amount = Generic::secure($_GET['amount']);
        $fund_id = Generic::secure($_GET['fund_id']);
        $user = new User();

        $fund = $user->GetFundingById($fund_id);
        if (!empty($fund)) {
            $admin_com = 0;
            if (!empty($config['donate_percentage']) && is_numeric($config['donate_percentage']) && $config['donate_percentage'] > 0) {
                $admin_com = ($config['donate_percentage'] * $amount) / 100;
                $amount = $amount - $admin_com;
            }
            $db->where('user_id',$fund->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
            $db->insert(T_FUNDING_RAISE,array('user_id' => $me['user_id'],
                                              'funding_id' => $fund_id,
                                              'amount' => $amount,
                                              'time' => time()));
            
            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'donate',
                                      'time' => time(),
                                      'admin_com' => $admin_com));
            $notif   = new Notifications();
            $hashed_id = $fund_id;
            if (!empty($fund->hashed_id)) {
                $hashed_id = $fund->hashed_id;
            }
            if ($fund->user_id != $me['user_id']) {

                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $fund->user_id,
                    'type' => 'donated',
                    'url' => $config['site_url'] . "/funding/".$hashed_id,
                    'time' => time()
                );
                try {
                    $notif->notify($re_data);
                } catch (Exception $e) {
                }

                
            }

            header("Location: " . $config['site_url'] . "/funding/".$hashed_id);
            exit();
        }
        else{
            header("Location: " . $config['site_url'] . "/oops");
            exit();
        }
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if ($action == 'stripe_donate' && IS_LOGGED && $config['credit_card'] == 'on' && !empty($config['stripe_id']) && !empty($config['stripe_id'])) {
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0 && !empty($_POST['fund_id']) && is_numeric($_POST['fund_id']) && $_POST['fund_id'] > 0) {
        require_once('sys/import3p/stripe-php-3.20.0/vendor/autoload.php');
        $stripe = array(
          "secret_key"      =>  $config['stripe_secret'],
          "publishable_key" =>  $config['stripe_id']
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $token = $_POST['stripeToken']; 

        $amount = Generic::secure($_POST['amount']);
        $fund_id = Generic::secure($_POST['fund_id']);
        $user = new User();

        $fund = $user->GetFundingById($fund_id);
        if (!empty($fund)) {
            try {
                $customer = \Stripe\Customer::create(array(
                    'source' => $token
                ));
                $charge   = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount' => $_POST['amount'].'00',
                    'currency' => 'usd'
                ));
                if ($charge) {
                    $admin_com = 0;
                    if (!empty($config['donate_percentage']) && is_numeric($config['donate_percentage']) && $config['donate_percentage'] > 0) {
                        $admin_com = ($config['donate_percentage'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }

                    $db->where('user_id',$fund->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $db->insert(T_FUNDING_RAISE,array('user_id' => $me['user_id'],
                                                      'funding_id' => $fund_id,
                                                      'amount' => $amount,
                                                      'time' => time()));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'donate',
                                      'time' => time(),
                                      'admin_com' => $admin_com));

                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $fund->user_id,
                        'type' => 'donated',
                        'url' => $config['site_url'] . "/funding/".$fund_id,
                        'time' => time()
                    );

                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data = array(
                        'status' => 200
                    );
                }
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'error' => $e->getMessage()
                );
            }
        }
    }

    
    
}

if ($action == 'cashfree' && $config['cashfree_payment'] == 'yes') {
	if (!empty($_POST['name']) && !empty($_POST['phone']) && !empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
		
		$result = array();
	    $order_id = uniqid();
	    $name = Generic::secure($_POST['name']);
	    $email = Generic::secure($_POST['email']);
	    $phone = Generic::secure($_POST['phone']);
	    $price = $org_amount = Generic::secure($_POST['amount']);
        if (!empty($config['currency_array']) && in_array($config['cashfree_currency'], $config['currency_array']) && $config['cashfree_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['cashfree_currency']])) {
            $price= (($price * $config['exchange'][$config['cashfree_currency']]));
            $price = round($price, 2);
        }

	    $callback_url = $config['site_url'] . "/aj/go_pro/cashfree_paid?amount=".$org_amount;


	    $secretKey = $config['cashfree_secret_key'];
		$postData = array( 
		  "appId" => $config['cashfree_client_key'], 
		  "orderId" => "order".$order_id, 
		  "orderAmount" => $price, 
		  "orderCurrency" => $config['cashfree_currency'], 
		  "orderNote" => "", 
		  "customerName" => $name, 
		  "customerPhone" => $phone, 
		  "customerEmail" => $email,
		  "returnUrl" => $callback_url, 
		  "notifyUrl" => $callback_url,
		);
		 // get secret key from your config
		 ksort($postData);
		 $signatureData = "";
		 foreach ($postData as $key => $value){
		      $signatureData .= $key.$value;
		 }
		 $signature = hash_hmac('sha256', $signatureData, $secretKey,true);
		 $signature = base64_encode($signature);
		 $cashfree_link = 'https://test.cashfree.com/billpay/checkout/post/submit';
		 if ($config['cashfree_mode'] == 'live') {
		 	$cashfree_link = 'https://www.cashfree.com/checkout/post/submit';
		 }

		$form = '<form id="redirectForm" method="post" action="'.$cashfree_link.'"><input type="hidden" name="appId" value="'.$config['cashfree_client_key'].'"/><input type="hidden" name="orderId" value="order'.$order_id.'"/><input type="hidden" name="orderAmount" value="'.$price.'"/><input type="hidden" name="orderCurrency" value="INR"/><input type="hidden" name="orderNote" value=""/><input type="hidden" name="customerName" value="'.$name.'"/><input type="hidden" name="customerEmail" value="'.$email.'"/><input type="hidden" name="customerPhone" value="'.$phone.'"/><input type="hidden" name="returnUrl" value="'.$callback_url.'"/><input type="hidden" name="notifyUrl" value="'.$callback_url.'"/><input type="hidden" name="signature" value="'.$signature.'"/></form>';
		$data['status'] = 200;
		$data['html'] = $form;
	}
	else{
		$data['message'] = lang('unknown_error');
	}
}

if ($action == 'cashfree_paid' && $config['cashfree_payment'] == 'yes' && IS_LOGGED ) {
	if (empty($_POST['txStatus']) || $_POST['txStatus'] != 'SUCCESS') {
		header('Location: ' . $config['site_url'] . '/go_pro');
        exit();
	}
    $orderId = $_POST["orderId"];
    $amount = Generic::secure($_GET["amount"]);
	$orderAmount = $_POST["orderAmount"];
	$referenceId = $_POST["referenceId"];
	$txStatus = $_POST["txStatus"];
	$paymentMode = $_POST["paymentMode"];
	$txMsg = $_POST["txMsg"];
	$txTime = $_POST["txTime"];
	$signature = $_POST["signature"];
	$data = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
	$hash_hmac = hash_hmac('sha256', $data, $config['cashfree_secret_key'], true) ;
	$computedSignature = base64_encode($hash_hmac);
	if ($signature == $computedSignature) {
        $wallet = $me['wallet'] + $amount;
        $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'Advertise',
                                      'time' => time()));
        if (!empty($_COOKIE['redirect_page'])) {
            $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
            $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
            header("Location: " . $redirect_page);
        }
        else{
            header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
        }
        exit();

    } else {
        header('Location: ' . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
        exit();
    }
}


if ($action == 'iyzipay' && ($config['iyzipay_payment'] == "yes" && !empty($config['iyzipay_key']) && !empty($config['iyzipay_secret_key'])) && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
    require_once("sys/import3p/iyzipay/samples/config.php");

	$amount = $org_amount = Generic::secure($_POST['amount']);
    if (!empty($config['currency_array']) && in_array($config['iyzipay_currency'], $config['currency_array']) && $config['iyzipay_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['iyzipay_currency']])) {
        $amount= (($amount * $config['exchange'][$config['iyzipay_currency']]));
    }
	$callback_url = $config['site_url'] . "aj/go_pro/iyzipay_paid?amount=".$org_amount;

	
	$request->setPrice($amount);
	$request->setPaidPrice($amount);
	$request->setCallbackUrl($callback_url);
	

	$basketItems = array();
	$firstBasketItem = new \Iyzipay\Model\BasketItem();
	$firstBasketItem->setId("BI".rand(11111111,99999999));
	$firstBasketItem->setName("Top Up Wallet");
	$firstBasketItem->setCategory1("Top Up Wallet");
	$firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
	$firstBasketItem->setPrice($amount);
	$basketItems[0] = $firstBasketItem;
	$request->setBasketItems($basketItems);
	$checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, Config::options());
    $content = $checkoutFormInitialize->getCheckoutFormContent();
	if (!empty($content)) {
		$db->where('user_id',$me['user_id'])->update(T_USERS,array('conversation_id' => $ConversationId));
		$data['html'] = $content;
		$data['status'] = 200;
	}
	else{
		$data['message'] = lang('unknown_error');
	}
}

if ($action == 'iyzipay_paid' && $config['iyzipay_payment'] == "yes"){
	if (!empty($_POST['token']) && !empty($me['conversation_id']) && !empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
		require_once('assets/import/iyzipay/samples/config.php');

		# create request class
		$request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
		$request->setLocale(\Iyzipay\Model\Locale::TR);
		$request->setConversationId($me['conversation_id']);
		$request->setToken($_POST['token']);

		# make request
		$checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, Config::options());

		# print result
		if ($checkoutForm->getPaymentStatus() == 'SUCCESS') {
            $amount = Generic::secure($_GET['amount']);
            $wallet = $me['wallet'] + $amount;
            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'Advertise',
                                          'time' => time()));
            if (!empty($_COOKIE['redirect_page'])) {
                $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                header("Location: " . $redirect_page);
            }
            else{
                header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
            }
            exit();
		}
		else{
			header('Location: ' . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
	        exit();
		}
	}
	else{
		header('Location: ' . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
	    exit();
	}
}
if ($action == 'set' && IS_LOGGED){
    if (!empty($_GET['type']) && in_array($_GET['type'], array('pro','fund','store','image','video','subscribe'))) {
        if ($_GET['type'] == 'pro') {
            setcookie("redirect_page", $config['site_url'].'/go_pro', time() + (60 * 60), '/');
        }
        else if($_GET['type'] == 'fund' && !empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0){
            $fund_id = Generic::secure($_GET['id']);
            $fund = $user->GetFundingById($fund_id);
            if (!empty($fund) && !empty($fund->id)) {
                setcookie("redirect_page", $config['site_url'].'/funding/'.$fund->hashed_id, time() + (60 * 60), '/');
            }
        }
        else if($_GET['type'] == 'store' && !empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0){
            $id = Generic::secure($_GET['id']);
            $store_image = $db->arrayBuilder()->where('id',$id)->getOne(T_STORE);
            if (!empty($store_image)) {
                setcookie("redirect_page", $config['site_url'].'/store/'.$id, time() + (60 * 60), '/');
            }
        }
        else if(($_GET['type'] == 'image' || $_GET['type'] == 'video') && !empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0){
            $id = Generic::secure($_GET['id']);
            $post = $db->arrayBuilder()->where('post_id',$id)->getOne(T_POSTS);
            if (!empty($post)) {
                setcookie("redirect_page", $config['site_url'].'/post/'.$id, time() + (60 * 60), '/');
            }
        }
        else if($_GET['type'] == 'subscribe' && !empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0){
            $id = Generic::secure($_GET['id']);
            $user = $db->arrayBuilder()->where('user_id',$id)->getOne(T_USERS);
            if (!empty($user)) {
                setcookie("redirect_page", $config['site_url'].'/'.$user['username'], time() + (60 * 60), '/');
            }
        }
    }
    $data = array(
        'status' => 200
    );
}
if ($action == 'pay_using_wallet' && IS_LOGGED){
    $data = array('status' => 400);
    $price = 0;
    if (!empty($_GET['type']) && in_array($_GET['type'], array('pro','fund','store','unlock_image','unlock_video','subscribe'))) {
        if ($_GET['type'] == 'pro') {
            $update = $user->updateStatic($me['user_id'],array('is_pro' => 1,'verified' => 1));
            $amount = $config['pro_price'];
            $date   = time();

            $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'pro_member',
                                          'date' => $date));

            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'pro_member',
                                          'time' => $date));
            $wallet = $me['wallet'] - $amount;
            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
            $data['status']     = 200;
            $data['url']        = $config['site_url'] . "/upgraded";
        }
        elseif ($_GET['type'] == 'fund' && !empty($_GET['fund_id']) && is_numeric($_GET['fund_id']) && $_GET['fund_id'] > 0 && !empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
            $fund_id = Generic::secure($_GET['fund_id']);
            $fund = $user->GetFundingById($fund_id);
            $amount = Generic::secure($_GET['amount']);
            if (!empty($fund) && !empty($fund->id)) {
                $wallet = $me['wallet'] - $amount;
                $admin_com = 0;
                if (!empty($config['donate_percentage']) && is_numeric($config['donate_percentage']) && $config['donate_percentage'] > 0) {
                    $admin_com = ($config['donate_percentage'] * $amount) / 100;
                    $amount = $amount - $admin_com;
                }
                $db->where('user_id',$fund->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                $db->insert(T_FUNDING_RAISE,array('user_id' => $me['user_id'],
                                                  'funding_id' => $fund_id,
                                                  'amount' => $amount,
                                                  'time' => time()));
                
                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'donate',
                                          'time' => time(),
                                          'admin_com' => $admin_com));
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                $notif   = new Notifications();
                $hashed_id = $fund_id;
                if (!empty($fund->hashed_id)) {
                    $hashed_id = $fund->hashed_id;
                }
                if ($fund->user_id != $me['user_id']) {

                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $fund->user_id,
                        'type' => 'donated',
                        'url' => $config['site_url'] . "/funding/".$hashed_id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                }
                $data['status']     = 200;
                $data['url']        = $config['site_url'] . "/funding/".$hashed_id;
            }
        }
        elseif ($_GET['type'] == 'store' && !empty($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0 && !empty($_GET['license_id'])) {
            $id = Generic::secure($_GET['id']);
            $item_license = Generic::secure($_GET['license_id']);
            $store_image = $db->arrayBuilder()->where('id',$id)->getOne(T_STORE);
            if (!empty($store_image)) {
                $license_options = unserialize($store_image['license_options']);
                if (!empty($license_options[$item_license])) {
                    $amount = $license_options[$item_license];
                    $u = $db->arrayBuilder()->where('user_id',$store_image['user_id'])->getOne(T_USERS);
                    $commesion = $amount / 2;
                    $balance = $u['balance'] + $commesion;
                    $update = $user->updateStatic($store_image['user_id'],array('balance' => $balance));
                    $db->insert(T_TRANSACTIONS,array(
                        'user_id'       => $me['user_id'],
                        'amount'        => $amount,
                        'type'          => 'store',
                        'item_store_id' => $id,
                        'admin_com'     => $commesion,
                        'time'          => time(),
                        'item_license'  => $item_license
                        )
                    );

                    $db->where('id',$id)->update(T_STORE, array( 'sells' => $db->inc(1)));
                    $wallet = $me['wallet'] - $amount;
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                    $notif   = new Notifications();


                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $store_image['user_id'],
                        'type' => 'store_purchase',
                        'url' => $config['site_url'] . "/store/".$id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data['status']     = 200;
                    $data['url']        = $config['site_url'] . "/store/".$id;
                }
            }
        }
        elseif ($_GET['type'] == 'unlock_image' && !empty($_GET['post_id']) && is_numeric($_GET['post_id']) && $_GET['post_id'] > 0 && $config['private_photos'] == 'on') {
            $post_id = Generic::secure($_GET['post_id']);
            $post = $db->where('post_id',$post_id)->getOne(T_POSTS);
            if (!empty($post) && $post->user_id != $me['user_id'] && !empty($post->price)) {
                $is_bought = $db->where('post_id',$post_id)->where('type','unlock image')->getValue(T_TRANSACTIONS,'COUNT(*)');
                if ($is_bought < 1) {
                    $amount = $post->price;
                    $admin_com = 0;
                    if ($config['private_photos_commission'] > 0) {
                        $admin_com = ($config['private_photos_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $wallet = $me['wallet'] - $post->price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'post_id' => $post_id,
                                              'type' => 'unlock image',
                                              'time' => time(),
                                              'admin_com' => $admin_com));
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                    $db->where('user_id',$post->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'unlock_user_image',
                        'url' => $config['site_url'] . "/post/".$post_id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data['url']        = $config['site_url'].'/post/'.$post_id;
                    $data['status']     = 200;
                }
                else{
                    $data = array(
                        'status' => 400,
                        'message' => lang('you_already_bought_this_post')
                    );
                }
            }
            else{
                $data = array(
                    'status' => 400,
                    'message' => lang('post_not_for_sell')
                );
            }
        }
        elseif ($_GET['type'] == 'unlock_video' && !empty($_GET['post_id']) && is_numeric($_GET['post_id']) && $_GET['post_id'] > 0 && $config['private_videos'] == 'on') {
            $post_id = Generic::secure($_GET['post_id']);
            $post = $db->where('post_id',$post_id)->getOne(T_POSTS);
            if (!empty($post) && $post->user_id != $me['user_id'] && !empty($post->price)) {
                $is_bought = $db->where('post_id',$post_id)->where('type','unlock video')->getValue(T_TRANSACTIONS,'COUNT(*)');
                if ($is_bought < 1) {
                    $amount = $post->price;
                    $admin_com = 0;
                    if ($config['private_videos_commission'] > 0) {
                        $admin_com = ($config['private_videos_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $wallet = $me['wallet'] - $post->price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'post_id' => $post_id,
                                              'type' => 'unlock video',
                                              'time' => time(),
                                              'admin_com' => $admin_com));
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                    $db->where('user_id',$post->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'unlock_user_video',
                        'url' => $config['site_url'] . "/post/".$post_id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data['url']        = $config['site_url'].'/post/'.$post_id;
                    $data['status']     = 200;
                }
                else{
                    $data = array(
                        'status' => 400,
                        'message' => lang('you_already_bought_this_post')
                    );
                }
            }
            else{
                $data = array(
                    'status' => 400,
                    'message' => lang('post_not_for_sell')
                );
            }
        }
        elseif ($_GET['type'] == 'subscribe' && !empty($_GET['user_id']) && is_numeric($_GET['user_id']) && $_GET['user_id'] > 0 && ($config['private_videos'] == 'on' || $config['private_photos'] == 'on')) {
            $user_id = Generic::secure($_GET['user_id']);
            $user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
            if (!empty($user_data) && $user_data->user_id != $me['user_id'] && !empty($user_data->subscribe_price)) {
                $month = 60 * 60 * 24 * 30;
                $is_subscribed = $db->where('user_id',$user_data->user_id)->where('subscriber_id',$me['user_id'])->where('time',(time() - $month),'>=')->getValue(T_SUBSCRIBERS,'COUNT(*)');
                if ($is_subscribed < 1) {
                    $amount = $user_data->subscribe_price;
                    $admin_com = 0;
                    if ($config['monthly_subscribers_commission'] > 0) {
                        $admin_com = ($config['monthly_subscribers_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $wallet = $me['wallet'] - $user_data->subscribe_price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'subscription_id' => $user_data->user_id,
                                              'type' => 'subscribe',
                                              'time' => time(),
                                              'admin_com' => $admin_com));
                    $db->insert(T_SUBSCRIBERS,array('user_id' => $user_data->user_id,
                                                    'subscriber_id' => $me['user_id'],
                                                    'time' => time()));
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                    $db->where('user_id',$user_data->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $user_data->user_id,
                        'type' => 'have_new_subscriber',
                        'url' => $config['site_url'] . "/".$me['username'],
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data['url']        = $config['site_url'].'/'.$user_data->username;
                    $data['status']     = 200;
                }
                else{
                    $data = array(
                        'status' => 400,
                        'message' => lang('you_already_subscribed')
                    );
                }
            }
            else{
                $data = array(
                    'status' => 400,
                    'message' => lang('user_dont_have_subscribe')
                );
            }
        }
    }
}
if ($action == 'stripe_session' && IS_LOGGED) {
    $data = array('status' => 400);
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        require_once('sys/stripe_config.php');
        $amount = Generic::secure($_POST['amount']);
        $org_amount = $amount;
        if (!empty($config['currency_array']) && in_array($config['stripe_currency'], $config['currency_array']) && $config['stripe_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['stripe_currency']])) {
            $amount= (($amount * $config['exchange'][$config['stripe_currency']]));
        }
        $amount = round($amount, 2) * 100;
        $payment_method_types = array('card');
        try {
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => [implode(',', $payment_method_types)],
                'line_items' => [[
                  'price_data' => [
                    'currency' => $config['stripe_currency'],
                    'product_data' => [
                      'name' => 'Top Up Wallet',
                    ],
                    'unit_amount' => $amount,
                  ],
                  'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $config['site_url'] . "/aj/go_pro/stripe_wallet_success&amount=".$org_amount,
                'cancel_url' =>  $config['site_url'] . "/aj/go_pro/stripe_wallet_cancel&amount=".$org_amount,
            ]);
            if (!empty($checkout_session) && !empty($checkout_session['id'])) {
                $db->where('user_id',$me['user_id'])->update(T_USERS,array('StripeSessionId' => $checkout_session['id']));
                $data = array(
                    'status' => 200,
                    'sessionId' => $checkout_session['id']
                );
            }
            else{
                $data = array(
                    'status' => 400,
                    'message' => lang("something_went_wrong_please_try_again_later_")
                );
            }
        }
        catch (Exception $e) {
            $data = array(
                'status' => 400,
                'message' => $e->getMessage()
            );
        }
    }
}
if ($action == 'stripe_wallet_success' && IS_LOGGED) {
    if (!empty($me['StripeSessionId']) && !empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
        require_once('sys/stripe_config.php');
        try {
            $checkout_session = \Stripe\Checkout\Session::retrieve($me['StripeSessionId']);
            if ($checkout_session->payment_status == 'paid') {
                $db->where('user_id',$me['user_id'])->update(T_USERS,array('StripeSessionId' => ''));
                //$amount = ($checkout_session->amount_total / 100);
                $amount = Generic::secure($_GET['amount']);
                $wallet = $me['wallet'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'type' => 'Advertise',
                                              'time' => time()));
                if (!empty($_COOKIE['redirect_page'])) {
                    $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                    $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                    header("Location: " . $redirect_page);
                }
                else{
                    header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
                }
                exit();
            }
            else{
                header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
                exit();
            }
            
        } catch (Exception $e) {
            header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
            exit();
        }
    }
    header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
    exit();
}
if ($action == 'stripe_wallet_cancel') {
    $db->where('user_id',$me['user_id'])->update(T_USERS,array('StripeSessionId' => ''));
    header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
    exit();
}
if ($action == 'authorize') {
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        require_once('sys/import3p/authorize/vendor/autoload.php');
        $amount = Generic::secure($_POST['amount']);
        $APILoginId = $config['authorize_login_id'];
        $APIKey = $config['authorize_transaction_key'];
        $refId = 'ref' . time();
        define("AUTHORIZE_MODE", $config['authorize_test_mode']);
        
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($APILoginId);
        $merchantAuthentication->setTransactionKey($APIKey);

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($_POST['card_number']);
        $creditCard->setExpirationDate($_POST['card_year'] . "-" . $_POST['card_month']);
        $creditCard->setCardCode($_POST['card_cvc']);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setPayment($paymentType);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        if ($config['authorize_test_mode'] == 'SANDBOX') {
            $Aresponse = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        }
        else{
            $Aresponse = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
        
        if ($Aresponse != null) {
            if ($Aresponse->getMessages()->getResultCode() == 'Ok') {
                $trans = $Aresponse->getTransactionResponse();
                if ($trans != null && $trans->getMessages() != null) {
                    $wallet = $me['wallet'] + $amount;
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                                  'amount' => $amount,
                                                  'type' => 'Advertise',
                                                  'time' => time()));
                    if (!empty($_COOKIE['redirect_page'])) {
                        $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                        $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                        $data['url'] = $redirect_page;
                    }
                    else{
                        $data['url'] = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
                    }
                    $data['status'] = 200;
                }
                else{
                    $error = lang("something_went_wrong_please_try_again_later_");
                    if ($trans->getErrors() != null) {
                        $error = $trans->getErrors()[0]->getErrorText();
                    }
                    $data['status'] = 400;
                    $data['error'] = $error;
                }
            }
            else{
                $trans = $Aresponse->getTransactionResponse();
                $error = lang("something_went_wrong_please_try_again_later_");
                if (!empty($trans) && $trans->getErrors() != null) {
                    $error = $trans->getErrors()[0]->getErrorText();
                }
                $data['status'] = 400;
                $data['error'] = $error;
            }
        }
        else{
            $data['status'] = 400;
            $data['error'] = lang("please_check_the_details");
        }
    }
    else{
        $data['status'] = 400;
        $data['error'] = lang('amount_empty');
    }
}
if ($action == 'securionpay_token') {
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $amount = Generic::secure($_POST['amount']);
        require_once('sys/import3p/securionpay/vendor/autoload.php');
        $securionPay = new SecurionPayGateway($config['securionpay_secret_key']);
        $user_key = rand(1111,9999).rand(11111,99999);

        $checkoutCharge = new CheckoutRequestCharge();
        $checkoutCharge->amount(($amount * 100))->currency('USD')->metadata(array('user_key' => $user_key));

        $checkoutRequest = new CheckoutRequest();
        $checkoutRequest->charge($checkoutCharge);

        $signedCheckoutRequest = $securionPay->signCheckoutRequest($checkoutRequest);
        if (!empty($signedCheckoutRequest)) {
            $db->where('user_id',$me['user_id'])->update(T_USERS,array('securionpay_key' => $user_key));
            $data['status'] = 200;
            $data['token'] = $signedCheckoutRequest;
        }
        else{
            $data['status'] = 400;
            $data['error'] = lang("please_check_the_details");
        }
    }
    else{
        $data['status'] = 400;
        $data['error'] = lang('amount_empty');
    }
}
if ($action == 'securionpay_handle') {
    if (!empty($_POST) && !empty($_POST['charge']) && !empty($_POST['charge']['id'])) {
        $url = "https://api.securionpay.com/charges?limit=10";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $config['securionpay_secret_key'].":password");
        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp,true);
        if (!empty($resp) && !empty($resp['list'])) {
            foreach ($resp['list'] as $key => $value) {
                if ($value['id'] == $_POST['charge']['id']) {
                    if (!empty($value['metadata']) && !empty($value['metadata']['user_key']) && !empty($value['amount'])) {
                        if ($me['securionpay_key'] == $value['metadata']['user_key']) {
                            $db->where('user_id',$me['user_id'])->update(T_USERS,array('securionpay_key' => 0));
                            $amount = intval(Generic::secure($value['amount'])) / 100;
                            $wallet = $me['wallet'] + $amount;
                            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                                          'amount' => $amount,
                                                          'type' => 'Advertise',
                                                          'time' => time()));
                            if (!empty($_COOKIE['redirect_page'])) {
                                $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                                $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                                $data['url'] = $redirect_page;
                            }
                            else{
                                $data['url'] = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
                            }
                            $data['status'] = 200;
                        }
                        else{
                            $data['status'] = 400;
                            $data['error'] = lang("something_went_wrong_please_try_again_later_");
                        }
                    }
                    else{
                        $data['status'] = 400;
                        $data['error'] = lang("something_went_wrong_please_try_again_later_");
                    }
                }
            }
        }
        else{
            $data['status'] = 400;
            $data['error'] = lang("something_went_wrong_please_try_again_later_");
        }
    }
    else{
        $data['status'] = 400;
        $data['error'] = lang("please_check_the_details");
    }
}
if ($action == 'coinpayments') {
    if (!empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
        try {
            require_once('sys/import3p/coinpayments.php');
            $amount = Generic::secure($_GET['amount']);
            $CP = new \MineSQL\CoinPayments();
            // Set the merchant ID and secret key (can be found in account settings on CoinPayments.net)
            $CP->setMerchantId($config['coinpayments_id']);
            $CP->setSecretKey($config['coinpayments_secret']);
            //REQUIRED
            $CP->setFormElement('currency', 'USD');
            $CP->setFormElement('amountf', $amount);
            $desc = 'Top Up Wallet';
            $CP->setFormElement('item_name', $desc);
            //OPTIONAL
            $CP->setFormElement('want_shipping', 0);
            $CP->setFormElement('user_id', $me['user_id']);
            $CP->setFormElement('ipn_url', $config['site_url'] . "/aj/go_pro/coinpayments_handle&amount=".$amount);
            $data = array(
                'status' => 200,
                'html' => $CP->createForm()
            );
        }
        catch (Exception $e) {
            $data = array(
                'status' => 400,
                'error' => $e->getMessage()
            );
        }
    }
    else{
        $data['status'] = 400;
        $data['error'] = lang('amount_empty');
    } 
}
if ($action == 'coinpayments_handle') {
    $data = array('status' => 400);
    if (!empty($_POST['amountf']) && is_numeric($_POST['amountf']) && $_POST['amountf'] > 0 && !empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
        try {
            require_once('sys/import3p/coinpayments.php');
            $CP = new \MineSQL\CoinPayments();
            $CP->setMerchantId($config['coinpayments_id']);
            $CP->setSecretKey($config['coinpayments_secret']);
            if ($CP->listen($_POST, $_SERVER)) {
                $amount = Generic::secure($_POST['amountf']);
                $user_id = Generic::secure($_POST['user_id']);
                $_user = new User();
                $user_data   = $_user->getUserDataById($user_id);
                $wallet = $user_data->wallet + $amount;
                $update = $_user->updateStatic($user_id,array('wallet' => $wallet));

                $db->insert(T_TRANSACTIONS,array('user_id' => $user_id,
                                              'amount' => $amount,
                                              'type' => 'Advertise',
                                              'time' => time()));
                if (!empty($_COOKIE['redirect_page'])) {
                    $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                    $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                    $url = $redirect_page;
                }
                else{
                    $url = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
                }
                header("Location: " . $url);
                exit();
                
            } else {
                // the payment is pending. an exception is thrown for all other payment errors.
                $data = array(
                    'status' => 400,
                    'error' => 'the payment is pending.'
                );
            }
        }
        catch (Exception $e) {
            $data = array(
                'status' => 400,
                'error' => $e->getMessage()
            );
        }
    }
}
if ($action == 'coinbase') {
    if (!empty($_GET['amount']) && is_numeric($_GET['amount']) && $_GET['amount'] > 0) {
        try {

            $amount = Generic::secure($_GET['amount']);
            $coinbase_hash = rand(1111,9999).rand(11111,99999);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $postdata =  array('name' => 'Top Up Wallet','description' => 'Top Up Wallet','pricing_type' => 'fixed_price','local_price' => array('amount' => $amount , 'currency' => $config['currency']), 'metadata' => array('coinbase_hash' => $coinbase_hash),"redirect_url" => $config['site_url'] . "/aj/go_pro/coinbase_handle?coinbase_hash=".$coinbase_hash,'cancel_url' => $config['site_url'] . "/aj/go_pro/coinbase_cancel?coinbase_hash=".$coinbase_hash);


            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postdata));

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'X-Cc-Api-Key: '.$config['coinbase_key'];
            $headers[] = 'X-Cc-Version: 2018-03-22';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $data = array(
                    'status' => 400,
                    'error' => curl_error($ch)
                );
            }
            curl_close($ch);
            $result = json_decode($result,true);
            if (!empty($result) && !empty($result['data']) && !empty($result['data']['hosted_url']) && !empty($result['data']['id']) && !empty($result['data']['code'])) {
                $user->updateStatic($me['user_id'],array('coinbase_hash' => $coinbase_hash,
                                                         'coinbase_code' => $result['data']['code']));
                $data['status'] = 200;
                $data['url'] = $result['data']['hosted_url'];
            }
        }
        catch (Exception $e) {
            $data = array(
                'status' => 400,
                'error' => $e->getMessage()
            );
        }
    }
    else{
        $data['status'] = 400;
        $data['error'] = lang('amount_empty');
    } 
}
if ($action == 'coinbase_handle') {
    if (!empty($_GET['coinbase_hash']) && is_numeric($_GET['coinbase_hash'])) {
        $coinbase_hash = Generic::secure($_GET['coinbase_hash']);
        $user_data = $db->where('coinbase_hash',$coinbase_hash)->getOne(T_USERS);
        if (!empty($user_data)) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges/'.$user_data->coinbase_code);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'X-Cc-Api-Key: '.$config['coinbase_key'];
            $headers[] = 'X-Cc-Version: 2018-03-22';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $url = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
                header("Location: " . $url);
                exit();
            }
            curl_close($ch);
            $result = json_decode($result,true);
            $update_data = array('coinbase_hash' => '',
                                 'coinbase_code' => '');
            if (!empty($result) && !empty($result['data']) && !empty($result['data']['pricing']) && !empty($result['data']['pricing']['local']) && !empty($result['data']['pricing']['local']['amount']) && !empty($result['data']['payments']) && !empty($result['data']['payments'][0]['status']) && $result['data']['payments'][0]['status'] == 'CONFIRMED') {

                $amount = (int)$result['data']['pricing']['local']['amount'];
                $wallet = $user_data->wallet + $amount;
                $update_data['wallet'] = $wallet;
                $db->insert(T_TRANSACTIONS,array('user_id' => $user_data->user_id,
                                          'amount' => $amount,
                                          'type' => 'Advertise',
                                          'time' => time()));
                if (!empty($_COOKIE['redirect_page'])) {
                    $redirect_page = preg_replace('/on[^<>=]+=[^<>]*/m', '', $_COOKIE['redirect_page']);
                    $redirect_page = preg_replace('/\((.*?)\)/m', '', $redirect_page);
                    $url = $redirect_page;
                }
                else{
                    $url = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
                }
                $user->updateStatic($user_data->user_id,$update_data);
                header("Location: " . $url);
                exit();
            }
            $user->updateStatic($user_data->user_id,$update_data);
        }
    }
    $url = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
    header("Location: " . $url);
    exit();
}
if ($action == 'coinbase_cancel') {
    if (!empty($_GET['coinbase_hash']) && is_numeric($_GET['coinbase_hash'])) {
        $coinbase_hash = Generic::secure($_GET['coinbase_hash']);
        $user_data = $db->where('coinbase_hash',$coinbase_hash)->getOne(T_USERS);
        if (!empty($user_data)) {
            $user->updateStatic($user_data->user_id,array('coinbase_hash' => '',
                                                          'coinbase_code' => ''));
        }
    }
    $url = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
    header("Location: " . $url);
    exit();
}
if ($action == 'unsubscribe') {
    $data['status']     = 400;
    if (!empty($_GET['user_id']) && is_numeric($_GET['user_id']) && $_GET['user_id'] > 0) {
        $user_id = Generic::secure($_GET['user_id']);
        $user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
        if (!empty($user_data)) {
            $db->where('user_id',$user_data->user_id)->where('subscriber_id',$me['user_id'])->delete(T_SUBSCRIBERS);
            $data['url']        = $config['site_url'].'/'.$user_data->username;
            $data['status']     = 200;
        }
        else{
            $data['message'] = lang("something_went_wrong_please_try_again_later_");
        }
    }
    else{
        $data['message'] = lang("something_went_wrong_please_try_again_later_");
    }
}