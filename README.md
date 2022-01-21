# WebPushって何？
- Android端末とかChromeブラウザで、「通知を受け取る」にしておくと、サーバーサイドから通知をプッシュできる仕組みです。
- これを使って、自分サーバー上に通知サイトを用意して、サーバーからのいろんな通知をスマホで受け取ろうという実験です。
- （たとえば、サーバー障害発生などでZabbixのアラートが上がったら自分のスマホに知らせてくれる、的なものです）

# さっそく実装
- 続きは GitHub で

 https://github.com/iruka-/WebNotifySample

# インストール前提条件
- 自分サーバーはSSL有効でなければなりません。
- Apache + PHP7 が動いているものとします。
- Apacheの設置先ディレクトリは、説明の都合上、 /public/ とします。

# インストール方法
- （１）まず、 上記 GitHubからコードを拾ってください
- サイト公開鍵と秘密鍵を準備します。
- （２）PHPのcomposer を使って、minishlink/web-push をインストールします。

$ composer require minishlink/web-push

- （３）以下のサイトを使用して、鍵を作成したのち、
- --- > https://web-push-codelab.glitch.me/
- 公開鍵は mysite.js に、秘密鍵は、PushMessage.php にそれぞれ転記してください。（コメント通り）
- （４）mysite.js と worker.js で https://example.com/ となっている行を、自分のサイトのURLに書き換えてください。
- （５）./log/ ディレクトリのパーミッションを www-data に対して書き込み許可にしてください
以上で設置は終わりです

# 動作チェック
- https://自分のサイトのURL/public/  に、パソコンのChromeもしくはスマホからアクセスします。
- （iPhoneは使えません）
- 「Web通知を許可する」のリンクを踏んで、通知を許可してください。
- 一番下の「Web通知のテスト」ボタンで、１０秒後に通知が届けば成功です。

# サーバー側から通知を送るには・・・
 $ php PushMessage.php "送りたいメッセージテキスト"

- で送れます。（送れるはずです）

# 参考にしたサイト
-JSとPHPでWebPushを送信するWebアプリケーションを作ってみる

https://zenn.dev/nnahito/articles/fd2c8b0ad0d19a

上記サイトを参考にさせていただきました。ありがとうございます。
