// サービスワーカー

// プッシュ通知をクリックしたときにブラウザを起動して表示するURL
const mysite = {
	openURL:'https://example.com/public/click.html'
};

// プッシュ通知を受け取ったとき
self.addEventListener('push', function (event) {
    const title = 'Push通知テスト';
    const options = {
        body:  event.data.text(),
        tag:    title,
        icon:  'icon.png',
        badge: 'icon.png'
    };
    event.waitUntil(self.registration.showNotification(title, options));
});
// プッシュ通知をクリックしたとき
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(
	    clients.openWindow(mysite.openURL)
    );
});
self.addEventListener('install', (event) => {} );
self.addEventListener('fetch', function(e) {} );
