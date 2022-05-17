<?php
if ($action == 'generate_order' && IS_LOGGED && $config['razorpay'] == "on" && !empty($config['razorpay_key']) && !empty($config['razorpay_secret'])) {
    $url = 'https://api.razorpay.com/v1/orders';
    $key_id = $config['razorpay_key'];
    $key_secret = $config['razorpay_secret'];
    //cURL Request
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'amount' => Generic::secure($_POST['amount'])*100,
		'currency' => 'INR',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $request = curl_exec ($ch);
    curl_close ($ch);
    $tranx = json_decode($request);
    $err = curl_error($ch);

    if($err){
        $data = array(
            'status' => 400,
            'message' => $tranx->error->description
        ); 
    }else{
        $data = array(
            'status' => 200,
            'message' => 'success',
            'order_id' => $tranx->id
        ); 
    }
}
else if ($action == 'proccess_payment' && IS_LOGGED && $config['razorpay'] == "on" && !empty($config['razorpay_key']) && !empty($config['razorpay_secret'])) {

    $payment_id = Generic::secure($_POST['payment_id']);
    $data = array(
        'amount' => Generic::secure($_POST['amount'])*100,
		'currency' => $config['currency'],
    );

    $url = 'https://api.razorpay.com/v1/payments/' . $payment_id . '/capture';
    $key_id = $config['razorpay_key'];
    $key_secret = $config['razorpay_secret'];
    $params = http_build_query($data);
    //cURL Request
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $request = curl_exec ($ch);
    curl_close ($ch);
    $tranx = json_decode($request);
    $err = curl_error($ch);

    if($err){
        $data = array(
            'status' => 400,
            'message' => $tranx->error->description
        ); 
    }else{
        if( $tranx->status == 'captured'){
            $type = Generic::secure($_POST['type']);
            $url = '';
            if($type == 'store'){
                $_amount = (int)$tranx->amount / 100;

                $item_id = Generic::secure($_POST['id']);
                $item_license = Generic::secure($_POST['license']);

                $store_image = $db->arrayBuilder()->where('id',$item_id)->getOne(T_STORE);
                $u = $db->arrayBuilder()->where('user_id',$store_image['user_id'])->getOne(T_USERS);
                $commesion = $_amount / 2;
                $wallet = $u['balance'] + $commesion;
                $update = $user->updateStatic($store_image['user_id'],array('balance' => $wallet));
                $db->insert(T_TRANSACTIONS,array(
                    'user_id'       => $me['user_id'],
                    'amount'        => $_amount,
                    'type'          => 'store',
                    'item_store_id' => $item_id,
                    'admin_com'     => $commesion,
                    'time'          => time(),
                    'item_license'  => $item_license
                    )
                );
                $db->where('id',$item_id)->update(T_STORE, array( 'sells' => $db->inc(1)));
                $notif   = new Notifications();
                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $store_image['user_id'],
                    'type' => 'store_purchase',
                    'url' => $config['site_url'] . "/store/".$item_id,
                    'time' => time()
                );
                try {
                    $notif->notify($re_data);
                } catch (Exception $e) {
                }
                $url = $config['site_url'] . "/store/".$item_id;
            }
            elseif ($type == 'pro'){
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
                $url = $config['site_url'] . "/upgraded";
            }
            elseif ($type == 'wallet') {
                $amount = (int)$tranx->amount / 100;
                $wallet = $me['wallet'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
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
            }

            $data = array(
                'status' => 200,
                'message' => 'success',
                'url' => $url
            ); 
        }else{
            $data = array(
                'status' => 400,
                'message' => 'error while proccess payment'
            ); 
        }
    }
}