<?php
/**

 *   Paystack Payment Gateway WHMCS Module
 *   Version: 1.0
 *   Build Date: 1 Feb 2017
 *   Author: AdebsAlert
 *   @copyright Copyright(c) Cregital Design Agency

************************************************************************/

//define the plan codes here:
$plan_1 = ['amount' => '10000', 'code' => 'PLN_w8lb3rhhoaqsdjb'];
$plan_2 = ['amount' => '15000', 'code' => 'PLN_jj71yzrximfjni9'];
$plan_3 = ['amount' => '30000', 'code' => 'PLN_k7mf9awrmhj3wfk'];


if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


function gatewaymodule_MetaData()
{
    return array(
        'DisplayName' => 'Paystack (Naira) Payment Gateway',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * @return array
 */
function paystack_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Paystack (Naira) Payment Gateway'
        ),
        'gatewayLogs' => array(
            'FriendlyName' => 'Gateway logs',
            'Type' => 'yesno',
            'Description' => 'Tick to enable gateway logs',
            'Default' => '0'
        ),
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
            'Default' => '0'
        ),
        'liveSecretKey' => array(
            'FriendlyName' => 'Live Secret Key',
            'Type' => 'text',
            'Size' => '32',
            'Default' => 'sk_live_xxx'
        ),
        'livePublicKey' => array(
            'FriendlyName' => 'Live Public Key',
            'Type' => 'text',
            'Size' => '32',
            'Default' => 'pk_live_xxx'
        ),
        'testSecretKey' => array(
            'FriendlyName' => 'Test Secret Key',
            'Type' => 'text',
            'Size' => '32',
            'Default' => 'sk_test_xxx'
        ),
        'testPublicKey' => array(
            'FriendlyName' => 'Test Public Key',
            'Type' => 'text',
            'Size' => '32',
            'Default' => 'pk_test_xxx'
        )
    );
}

/**
 * Payment link.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @return string
 */
function paystack_link($params)
{
    // Client
    $email = $params['clientdetails']['email'];
    $phone = $params['clientdetails']['phonenumber'];
    $params['langpaynow'] =
        array_key_exists('langpaynow', $params) ?
            $params['langpaynow'] : 'Pay with ATM' ;

    // Config Options
    if ($params['testMode'] == 'on') {
        $publicKey = $params['testPublicKey'];
        $secretKey = $params['testSecretKey'];
    } else {
        $publicKey = $params['livePublicKey'];
        $secretKey = $params['liveSecretKey'];
    }

    // check if there is an id in the GET meaning the invoice was loaded directly
    $paynowload = ( !array_key_exists('id', $_GET) );

    // Invoice
    $invoiceId = $params['invoiceid'];
    $amountinkobo = intval(floatval($params['amount'])*100);
    $currency = $params['currency'];


    //check the amount sent from the app to know the plan to be used
    if($params['amount'] == $plan_1['amount']){
        $plan = $plan_1['code'];
    }elseif($params['amount'] == $plan_2['amount']){
        $plan = $plan_2['code'];
    }elseif($params['amount'] == $plan_3['amount']){
        $plan = $plan_3['code'];
    }else{
        $plan = '';
    }


    if (!(strtoupper($currency) == 'NGN')) {
        return ("Paystack only accepts NGN payments for now.");
    }

    //check if plan is empty so we won't pass the plan param to the fallback query
    if($plan != ''){
        $isSSL = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
        $fallbackUrl = 'http' . ($isSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] .
            substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) .
            '/modules/gateways/callback/paystack.php?' .
            http_build_query(array(
                'invoiceid'=>$invoiceId,
                'email'=>$email,
                'phone'=>$phone,
                'amountinkobo'=>$amountinkobo,
                'plan'=>$plan,
                'go'=>'standard'
            ));

        var_dump($plan_3); exit;
    }else{
        $isSSL = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
        $fallbackUrl = 'http' . ($isSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] .
            substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) .
            '/modules/gateways/callback/paystack.php?' .
            http_build_query(array(
                'invoiceid'=>$invoiceId,
                'email'=>$email,
                'phone'=>$phone,
                'amountinkobo'=>$amountinkobo,
                'go'=>'standard'
            ));
    }



    //check if plan is empty so we won't pass the plan param to the callback query
    if($plan != ''){
        $callbackUrl = 'http' . ($isSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] .
            substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) .
            '/modules/gateways/callback/paystack.php?' .
            http_build_query(array(
                'invoiceid'=>$invoiceId
            ));

        $code = '
        <form target="hiddenIFrame" action="about:blank">
        <script src="https://js.paystack.co/v1/inline.js"></script>
        <div class="payment-btn-container2"></div>
        <script>
            // load jQuery 1.12.3 if not loaded
            (typeof $ === \'undefined\') && document.write("<scr" + "ipt type=\"text\/javascript\" '.
            'src=\"https:\/\/code.jquery.com\/jquery-1.12.3.min.js\"><\/scr" + "ipt>");
        </script>
        <script>
            $(function() {
                var paymentMethod = $(\'select[name="gateway"]\').val();
                if (paymentMethod === \'paystack\') {
                    $(\'.payment-btn-container2\').hide();
                    var toAppend = \'<button type="button"'.
            ' onclick="payWithPaystack()"> '.addslashes($params['langpaynow']).'</button>\';
                    $(\'.payment-btn-container\').append(toAppend);
                   if($(\'.payment-btn-container\').length===0){
                     $(\'select[name="gateway"]\').after(toAppend);
                   }
                }
            });
        </script>
    </form>
    
    <div class="hidden" style="display:none"><iframe name="hiddenIFrame"></iframe></div>
    <script>
        var paystackIframeOpened = false;
        var paystackHandler = PaystackPop.setup({
          key: \''.addslashes(trim($publicKey)).'\',
          email: \''.addslashes(trim($email)).'\',
          phone: \''.addslashes(trim($phone)).'\',
          amount: '.$amountinkobo.',
          plan: '.$plan.',
          callback: function(response){
            window.location.href = \''.addslashes($callbackUrl).'&trxref=\' + response.trxref;
          },
          onClose: function(){
              paystackIframeOpened = false;
          }
        });
        function payWithPaystack(){
            if (paystackHandler.fallback || paystackIframeOpened) {
              // Handle non-support of iframes or
              // Being able to click PayWithPaystack even though iframe already open
              window.location.href = \''.addslashes($fallbackUrl).'\';
            } else {
              paystackHandler.openIframe();
              paystackIframeOpened = true;
              $(\'img[alt="Loading"]\').hide();
              $(\'div.alert.alert-info.text-center\').html(\'Click the button below to retry payment...\');
              $(\'.payment-btn-container2\').append(\'<button type="button"'.
            ' onclick="payWithPaystack()">'.addslashes($params['langpaynow']).'</button>\');
            }
       }
       ' . ( $paynowload ? 'setTimeout("payWithPaystack()", 5100);' : '' ) . '
    </script>';
    }else{
        $callbackUrl = 'http' . ($isSSL ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] .
            substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) .
            '/modules/gateways/callback/paystack.php?' .
            http_build_query(array(
                'invoiceid'=>$invoiceId
            ));

        $code = '
        <form target="hiddenIFrame" action="about:blank">
        <script src="https://js.paystack.co/v1/inline.js"></script>
        <div class="payment-btn-container2"></div>
        <script>
            // load jQuery 1.12.3 if not loaded
            (typeof $ === \'undefined\') && document.write("<scr" + "ipt type=\"text\/javascript\" '.
            'src=\"https:\/\/code.jquery.com\/jquery-1.12.3.min.js\"><\/scr" + "ipt>");
        </script>
        <script>
            $(function() {
                var paymentMethod = $(\'select[name="gateway"]\').val();
                if (paymentMethod === \'paystack\') {
                    $(\'.payment-btn-container2\').hide();
                    var toAppend = \'<button type="button"'.
            ' onclick="payWithPaystack()"> '.addslashes($params['langpaynow']).'</button>\';
                    $(\'.payment-btn-container\').append(toAppend);
                   if($(\'.payment-btn-container\').length===0){
                     $(\'select[name="gateway"]\').after(toAppend);
                   }
                }
            });
        </script>
    </form>
    
    <div class="hidden" style="display:none"><iframe name="hiddenIFrame"></iframe></div>
    <script>
        var paystackIframeOpened = false;
        var paystackHandler = PaystackPop.setup({
          key: \''.addslashes(trim($publicKey)).'\',
          email: \''.addslashes(trim($email)).'\',
          phone: \''.addslashes(trim($phone)).'\',
          amount: '.$amountinkobo.',
          callback: function(response){
            window.location.href = \''.addslashes($callbackUrl).'&trxref=\' + response.trxref;
          },
          onClose: function(){
              paystackIframeOpened = false;
          }
        });
        function payWithPaystack(){
            if (paystackHandler.fallback || paystackIframeOpened) {
              // Handle non-support of iframes or
              // Being able to click PayWithPaystack even though iframe already open
              window.location.href = \''.addslashes($fallbackUrl).'\';
            } else {
              paystackHandler.openIframe();
              paystackIframeOpened = true;
              $(\'img[alt="Loading"]\').hide();
              $(\'div.alert.alert-info.text-center\').html(\'Click the button below to retry payment...\');
              $(\'.payment-btn-container2\').append(\'<button type="button"'.
            ' onclick="payWithPaystack()">'.addslashes($params['langpaynow']).'</button>\');
            }
       }
       ' . ( $paynowload ? 'setTimeout("payWithPaystack()", 5100);' : '' ) . '
    </script>';
    }

    return $code;
}
