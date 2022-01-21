<?php

// ===================================================
// Web通知を送るコマンドラインインターフェース
//
// 使い方：
//  $ php PushMessage.php "送りたいメッセージテキスト"
// ===================================================

require_once 'vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

//  ここに、秘密鍵を入れてください.
const PRIVATE_KEY = '<<###ここに、秘密鍵を入れてください###>>';


function get_endpoints()
{
	$file = './log/client.txt';

	$eplist=array();
	if(! file_exists( $file )) { return $eplist; }

	$list = file_get_contents($file);

	$buf = explode("\n",$list);
	foreach($buf as $line) {
		$row = explode('<>',$line);
		if((count($row)==4)&&(strlen($line)>50)) {
			$key= $row[1];
			$tkn= $row[2];
			$ep = $row[3];
			$ee = [
			   'endpoint'  => $ep,
			   'authToken' => $tkn,
			   'publicKey' => $key 
			   ];
			array_push($eplist,$ee);
		}
	}
    return $eplist;
}

function mysite_config()
{
	$mysite=array();

	$file = './mysite.js';
	if(! file_exists( $file )) { return array(); }
	$list = file_get_contents($file);
	$buf = explode("\n",$list);
	foreach($buf as $line) {
		$s = preg_replace('/[ \t]+/','',$line);
		$s = preg_replace('/,$/'    ,'',$s);
		$pos = strpos($s,':');
		if($pos>0) {
			$s1 = substr($s,0,$pos);
			$s2 = substr($s,$pos+1);
			$s2 = preg_replace("/'/",'',$s2);
			$mysite[$s1]=$s2;
		}
	}
	return $mysite;
}
									    
function send_push($pushText)
{
	$dry = 0;
	$ret = '';
	$mysite = mysite_config();
	// ブラウザに認証させる
	$auth = array( 'VAPID' => array(
						 'subject'    => $mysite['siteURL'],
						 'publicKey'  => $mysite['appServerKey'],
						 'privateKey' => PRIVATE_KEY,
						           ),
				 );
	
	if( $pushText=='' ) {
		$pushText='Push通知.';
	}
	echo $pushText . "\n";

	$endpoints = get_endpoints();
//DEBUG:
//	print_r($mysite);
	print_r($auth);
//	print_r($endpoints);

	foreach($endpoints as $ep1) {
		$authtoken = $ep1['authToken'];
		//print_r($ep1);
		$subscription = Subscription::create($ep1);
		//print_r($subscription);
		if($dry==0) {
			$webPush = new WebPush($auth);
			$report = $webPush->sendOneNotification(
										    $subscription,
										    $pushText
										);
			echo $authtoken . ":";
			$ret = $report->getRequest()->getUri()->__toString();
			if ($report->isSuccess()) {
				echo "Push Success.\n";
			} else {
				echo "Push Fail.\n";
			}
		}
		$subscription = FALSE;
	}
}

send_push($argv[1]);
