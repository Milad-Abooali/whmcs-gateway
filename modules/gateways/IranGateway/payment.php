<?php
/**
 **************************************************************************
 * IranGateway Gateway
 * IranGateway.php
 * Send Request & Callback
 * @author           Milad Abooali <m.abooali@hotmail.com>
 * @version          1.0
 **************************************************************************
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedFunctionInspection
 * @noinspection PhpUndefinedMethodInspection
 * @noinspection PhpDeprecationInspection
 * @noinspection SpellCheckingInspection
 * @noinspection PhpIncludeInspection
 * @noinspection PhpIncludeInspection
 */

global $CONFIG;

$cb_gw_name    = 'IranGateway';
$cb_output     = ['POST'=>$_POST,'GET'=>$_GET];
$action 	   = isset($_GET['a']) ? $_GET['a'] : false;

$root_path     = '../../../';
$includes_path = '../../../includes/';
include($root_path.((file_exists($root_path.'init.php'))?'init.php':'dbconnect.php'));
include($includes_path.'functions.php');
include($includes_path.'gatewayfunctions.php');
include($includes_path.'invoicefunctions.php');

$modules       = getGatewayVariables($cb_gw_name);
if(!$modules['type']) die('Module Not Activated');

$invoice_id    = $_REQUEST['invoiceid'];
$amount_rial   = intval($_REQUEST['amount']);
$amount        = $amount_rial / $modules['cb_gw_unit'];
$callback_URL  = $CONFIG['SystemURL']."/modules/gateways/$cb_gw_name/payment.php?a=callback&invoiceid=". $invoice_id.'&amount='.$amount;
$invoice_URL  = $CONFIG['SystemURL']."/viewinvoice.php?id=".$invoice_id;

/**
 * Telegram Notify
 * @param $notify
 */
function notifyTelegram($notify) {
    global $modules;
    $row = "------------------";
    $pm= "\n".$row.$row.$row."\n".$notify['title']."\n".$row."\n".$notify['text'];
    $chat_id = $modules['cb_telegram_chatid'];
    $botToken = $modules['cb_telegram_bot'];
    $data = ['chat_id' => $chat_id, 'text' => $pm];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.telegram.org/bot$botToken/sendMessage");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);
    curl_close($curl);
}

/**
 * Email Notify
 * @param $notify
 */
function notifyEmail($notify) {
    global $modules;
    global $cb_output;
    $receivers = explode(',', $modules['cb_email_address']);
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/plain;charset=UTF-8" . "\r\n";
    $headers .= "From: ".$modules['cb_email_from']."\r\n";
    if($receivers) foreach ($receivers as $receiver)
        $cb_output['mail'][] = mail($receiver, $notify['title'], $notify['text'], $headers);
}

/**
 * Payment Failed
 * @param $log
 */
function payment_failed($log)
{
    global $modules;
    global $cb_gw_name;
    $log['status'] = "unpaid";
    $cb_output['payment_failed']=$log;
    logTransaction($modules["name"], $log, "ناموفق");
    if($modules['cb_email_on_error'] || $modules['cb_telegram_on_error']){
        $notify['title'] = $cb_gw_name . ' | ' . "تراکنش ناموفق";
        $notify['text'] = '';
        foreach ($log as $key=>$item)
            $notify['text'] .= "\n\r$key: $item";
        if ($modules['cb_email_on_error']) notifyEmail($notify);
        if ($modules['cb_telegram_on_error']) notifyTelegram($notify);
    }
}

/**
 * Payment Success
 * @param $log
 */
function payment_success($log)
{
    global $modules;
    global $cb_gw_name;
    $log['status'] = "OK";
    $cb_output['payment_success']=$log;
    logTransaction($modules["name"], $log, "موفق");
    if($modules['cb_email_on_success'] || $modules['cb_telegram_on_success']){
        $notify['title'] = $cb_gw_name . ' | ' . "تراکنش موفق";
        $notify['text'] = '';
        foreach ($log as $key=>$item)
            $notify['text'] .= "\n\r$key: $item";
        if ($modules['cb_email_on_success']) notifyEmail($notify);
        if ($modules['cb_telegram_on_success']) notifyTelegram($notify);
    }
}

/**
 * Redirecttion
 * @param $url
 */
function redirect($url)
{
    if (headers_sent())
        echo "<script>window.location.assign('$url')</script>";
    else
        header("Location: $url");
    exit;
}

if($action==='callback') {

    redirect($invoice_URL);
}
elseif ($action==='send'){

}