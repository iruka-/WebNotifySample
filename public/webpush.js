//サービスワーカーの登録
self.addEventListener('load', async () =>
{
	if ('serviceWorker' in navigator) {
		window.sw = await navigator.serviceWorker.register('worker.js', {scope: '/public/'});
	}
});

//WebPushを許可
async function allowWebPush()
{
	if ('Notification' in window) {
		let permission = Notification.permission;

		if (permission === 'denied') {
			alert('Push通知が拒否されているようです。ブラウザの設定からPush通知を有効化してください');
			return false;
		} else if (permission === 'granted') {
			alert('すでにWebPushを許可済みです');
			return false;
		}
	}
	// 取得したPublicKey
	const appServerKey = mysite.appServerKey;
	const applicationServerKey = urlB64ToUint8Array(appServerKey);

	// push managerにサーバーキーを渡し、トークンを取得
	let subscription = undefined;
	try {
		subscription = await window.sw.pushManager.subscribe({
			userVisibleOnly: true,applicationServerKey
		});
	} catch (e) {
		alert('Push通知機能が拒否されたか、エラーが発生しましたので、Push通知は送信されません。');
		return false;
	}


	// 必要なトークンを変換して取得（これが重要！！！）
	const key = subscription.getKey('p256dh');
	const token = subscription.getKey('auth');
	const request = {
		endpoint:      subscription.endpoint,
		userPublicKey: btoa(String.fromCharCode.apply(null, new Uint8Array(key))),
		userAuthToken: btoa(String.fromCharCode.apply(null, new Uint8Array(token)))
	};

	localStorage.setItem('key',btoa(String.fromCharCode.apply(null, new Uint8Array(key))));
	localStorage.setItem('token',btoa(String.fromCharCode.apply(null, new Uint8Array(token))));
	localStorage.setItem('request',subscription.endpoint);
	console.log(request);
	SendTokenData();
}



//トークンを変換するときに使うロジック
function urlB64ToUint8Array (base64String)
{
	const padding = '='.repeat((4 - base64String.length % 4) % 4);
	const base64 = (base64String + padding)
	               .replace(/\-/g, '+')
	               .replace(/_/g, '/');

	const rawData = window.atob(base64);
	const outputArray = new Uint8Array(rawData.length);

	for (let i = 0; i < rawData.length; ++i) {
		outputArray[i] = rawData.charCodeAt(i);
	}
	return outputArray;
}

//  Keyを見る
function onClickView()
{
	target = document.getElementById("output");
	//target.innerText = document.forms.confirm.id_textBox1.value;
	v=localStorage.getItem('key');
	t=localStorage.getItem('token');
	q=localStorage.getItem('request');
	if(v=='') {
		alert('Web通知を許可してください');
		return;
	}
	if(v==null) {
		alert('Web通知を許可してください');
		return;
	}
	alert('key: ' + v + "\n" +'token: ' + t + "\n"+'request: ' + q + "\n");
}

//  エンドポイントの通知キーをサーバーに送る.
function SendTokenData()
{
	console.log( 'Sending data' );
	v=localStorage.getItem('key');
	t=localStorage.getItem('token');
	q=localStorage.getItem('request');
	urlEncodedData = "{\n" + 'key:     ' + v + "\n" +'token:   ' + t + "\n"+'request: ' + q + "\n}\n";

	const request = new XMLHttpRequest();

	// データが正常に送信された場合に行うことを定義します
	request.addEventListener( 'load', function(event) {
//    alert( 'Web通知をサーバーに登録しました' );
	} );

	// エラーが発生した場合に行うことを定義します
	request.addEventListener( 'error', function(event) {
		alert( 'Oops! Something went wrong.' );
	} );

	// リクエストをセットアップします
	request.open( 'POST', mysite.siteURL + 'SendToken.php');

	// フォームデータの POST リクエストを扱うために必要な HTTP ヘッダを追加します
	request.setRequestHeader( 'Content-Type', 'application/json' );

	// 最後に、データを送信します
	request.send( urlEncodedData );
	request.onreadystatechange = function () {
		if (request.readyState != 4) {
			// リクエスト中
		} else if (request.status != 200) {
			// 失敗
		} else {
			// 取得成功
			var result = request.responseText;
			alert("Web通知をサーバーに登録しました\nサーバーの応答:\n" + result);
		}
	};
}

//  通知テストを呼び出す.
function NotifyTest()
{
	const request = new XMLHttpRequest();

	// エラーが発生した場合に行うことを定義します
	request.addEventListener( 'error', function(event) {
		alert( 'Oops! Something went wrong.' );
	} );

	// リクエストをセットアップします
	request.open( 'POST', mysite.siteURL + 'NotifyTest.php' );
	urlEncodedData = "{'_'}\n";
	// 最後に、データを送信します
	request.send( urlEncodedData );
	request.onreadystatechange = function () {
		if (request.readyState != 4) {
			// リクエスト中
		} else if (request.status != 200) {
			// 失敗
		} else {
			// 取得成功
			var result = request.responseText;
			alert("Web通知テスト要求を送信\nサーバーの応答:\n" + result);
		}
	};
}
