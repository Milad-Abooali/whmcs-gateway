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
