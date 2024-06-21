# 日記
こちらはPHP、MySQL、Apache2で動作するブログ的立ち位置の日記ツールです。（）

## 使い方
### index.php
index.phpを開いたら上のほうにあるservername、username、password、dbnameというところに上からホスト名、ユーザー名、パスワード、データベースの名前となっています。必ず書きましょう。  
```php:index.php
$servername = "";
$username = "";
$password = "";
$dbname = "";
```
日記ではFontawesomeというツールキットを使用しているため、そちらに登録する必要があります。JavaScriptを書き換えるだけで大丈夫です。
```php:index.php
<script src="https://kit.fontawesome.com/.js" crossorigin="anonymous"></script>
```

### MySQL
これはMySQLにposts.sqlをインポートすれば完了です。  

## 注意
- Fontawesomeというツールがないとアイコンが正常に表示されません。
- CSSは抜いてあるので、ご自身でカスタマイズする必要があります。
- 利用は自己責任でよろしくお願いします。
