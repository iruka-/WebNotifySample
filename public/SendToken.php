<?php
// ===================================================
// スマホ側で取得した通知キーをサーバーに保存する.
// ===================================================

// 保存するディレクトリ (www-dataで書き込み可能な場所)
$LOGDIR = 'log/';

// クライアント端末のエンドポイント情報を記録したファイル.
$CLIENT = $LOGDIR . 'client.txt';

// デバッグ用log print
function logprint($msg)
{
	global  $LOGDIR;
	$file = $LOGDIR . "debug-log.txt";
	error_log($msg . "\n" , 3 , $file);
}

//  JavaScriptから送られたjsonっぽいやつの値を取得.
function get_jsval($line)
{
	$n=strpos($line,': ');
	if($n>0) {                  #  0123456789
		return substr($line,9); # 'request: '
	}
	return '';
}

//  JavaScriptから送られたjsonっぽいやつを１行づつ解読.
function read_record($buffer)
{
	$nowdate = date("Y/m/d H:i:s", strtotime('now'));
	$row = explode("\n",$buffer);
	$key = '';
	$token = '';
	$request = '';
	$f=0;
	foreach ($row as $line) {
		if(strpos($line ,"key: ")===0)     {$key     = get_jsval($line);$f=$f+1;}
		if(strpos($line ,"token: ")===0)   {$token   = get_jsval($line);$f=$f+2;}
		if(strpos($line ,"request: ")===0) {$request = get_jsval($line);$f=$f+4;}
	}
	if($f==7) {
		//結果を、１行の昔のBBSっぽいテキストで返す.
		return "$nowdate<>$key<>$token<>$request\n";
	}else{
		//取得できず.
		return '';
	}
}
//  エンドポイント重複登録のチェック (1=重複).
function check_dup_record($records)
{
	global $CLIENT;
	$records = str_replace("\n","",$records);
	$arr = explode('<>',$records);
	$token  =$arr[2];
	$request=$arr[3];

	$file=file_get_contents($CLIENT);
	$buf = explode("\n",$file);
	foreach ($buf as $line) {
		if($buf != '') {
			$arr1=explode('<>',$line);
			if(count($arr1)==4) {
				$token1  =$arr1[2];
				$request1=$arr1[3];
				if($request == $request1) {
					if($token == $token1) {
						return 1; // DUP! 既に登録済の端末.
					}
				}
			}
		}
	}
	return 0; // OK.
}

//  エンドポイントの通知キーを log/client.txt に追記する.
function RegisterToken($req)
{
	global $CLIENT;
	$records = read_record($req);
	if($records == '') {
		return "EMPTY REQUEST.";
	}
	if( check_dup_record($records)) {
		return "DUP RECORD.";
	};

	$fp = fopen($CLIENT,"a+");
	fwrite($fp,$records);
	fclose($fp);

	return "OK.";
}

function phpmain()
{
	header("Content-Type: application/json; charset=utf-8");
	$req = file_get_contents("php://input");
	$ans = RegisterToken($req);
	// PCに返送する.
	print $ans . "\n";
}

phpmain();

?>
