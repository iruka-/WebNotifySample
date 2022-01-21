<?php
// ===================================================
// Web通知のテスト（10秒後に通知を送ります）
// ===================================================

function NotifyTest($req)
{
	if($req == '') {
		return "EMPTY REQUEST.";
	}
	$req = preg_replace('/\n/','',$req);
	if($req == "{'_'}") {
		$command = getcwd() . "/PushTest.sh >/dev/null 2>/dev/null &";
		$output=null;
		$retval=null;
		exec($command, $output, $retval);
		return "OK.";
	}
	return $req;
}

function phpmain()
{
	header("Content-Type: text/plain");
	$req = file_get_contents("php://input");
	$ans = NotifyTest($req);
	print $ans . "\n";
}

phpmain();

?>
