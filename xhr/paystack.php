<?php
if ($action == 'success' && IS_LOGGED && $config['paystack'] == "on" && !empty($config['paystack_secret_key']) && !empty($_GET['reference'])) {
    $payment  = CheckPaystackPayment($_GET['reference']);
    if ($payment) {
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
    else {
        header("Location: " . $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : ''));
        exit();
    }
}
else if($action == 'create_payment'){
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $amount = $org_amount = Generic::secure($_POST['amount']);
        if (!empty($config['currency_array']) && in_array($config['paystack_currency'], $config['currency_array']) && $config['paystack_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['paystack_currency']])) {
            $amount= (($amount * $config['exchange'][$config['paystack_currency']]));
        }
        $amount = $amount * 100;
        $callback_url = $config['site_url'] . "/aj/paystack/success&amount=".$org_amount; 
        $result = array();
        $reference = uniqid();

        //Set other parameters as keys in the $postdata array
        $postdata =  array('email' => $me['email'], 'amount' => $amount,"reference" => $reference,'callback_url' => $callback_url,'currency' => $config['paystack_currency']);
        $url = "https://api.paystack.co/transaction/initialize";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postdata));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
          'Authorization: Bearer '.$config['paystack_secret_key'],
          'Content-Type: application/json',

        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $request = curl_exec ($ch);

        curl_close ($ch);

        if ($request) {
            $result = json_decode($request, true);
            if (!empty($result)) {
                 if (!empty($result['status']) && $result['status'] == 1 && !empty($result['data']) && !empty($result['data']['authorization_url']) && !empty($result['data']['access_code'])) {
                    $db->where('user_id',$me['user_id'])->update(T_USERS,array('paystack_ref' => $reference));
                    $data['status'] = 200;
                    $data['url'] = $result['data']['authorization_url'];
                }
                else{
                    $data['status'] = 400;
                    $data['message'] = $result['message'];
                }
            }
            else{
                $data['status'] = 400;
                $data['message'] = lang("something_went_wrong_please_try_again_later_");
            }
        }
        else{
            $data['status'] = 400;
            $data['message'] = lang("something_went_wrong_please_try_again_later_");
        }
    }
}