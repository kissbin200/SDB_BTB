<?php
/**
 * 接收微信APP支付后的回调
 *
 * 
 */
error_reporting(7);
$_GET['act']    = 'payment';
$_GET['op']     = 'wxpay_app_notify';
require_once(dirname(__FILE__).'/../../../index.php');
?>