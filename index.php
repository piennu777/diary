<?php
$servername = "localhost";
$username = "piennu777";
$password = "Orion0411";
$dbname = "diary_db";

// データベースに接続
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("データベース接続エラー: " . $conn->connect_error);
}

// パスワードのセットアップ
$hashedPassword = password_hash("pass7019", PASSWORD_DEFAULT);

// 投稿フォームの表示判定
$adminMode = isset($_GET['admin']) && $_GET['admin'] == 'on';

// 投稿フォーム処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // パスワードの確認
    $providedPassword = $_POST['password'];
    if (password_verify($providedPassword, $hashedPassword)) {
        // パスワードが正しい場合
        $name = $_POST['name'];
        $tags = $_POST['tags'];
        $current_time = date("Y-m-d H:i:s");

        $time_blog = date("Y年m月d日");

        //内容
        $content = $_POST['content'];
        // 行ごとに改行を分割
        $lines = explode("\n", $content);

        // 各行に対して処理
        $content_with_line_breaks = '';
        foreach ($lines as $line) {
            if (!preg_match('/<\/[hH]3>|<\/[hH]4>/', trim($line))) {
                $content_with_line_breaks .= $line . '<br>';
            } else {
                $content_with_line_breaks .= $line;
            }
        }

        // 複数ファイルのアップロード処理
        $uploadDirectory = $tags . '/' . $name . '/';
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        foreach ($_FILES['image']['tmp_name'] as $key => $tempName) {
            if ($_FILES['image']['error'][$key] == UPLOAD_ERR_OK) {
                $originalName = $_FILES['image']['name'][$key];

                // ファイル名を一意に生成
                $filename = $uploadDirectory . $originalName;

                // アップロードされたファイルを移動
                move_uploaded_file($tempName, $filename);
            }
        }

        $uploadDirectory = $tags . '/' . $name . '/';
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        // ファイル名を一意に生成
        $filename = $uploadDirectory . 'index.html';

        // ブログの中身テンプレートを生成
        $blogContent = <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <title>$name</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <meta name="keywords" content="">
    <meta name="copyright" content="Copyright &copy; 2024 user. All rights reserved." />
</head>
<body>
    <main id="main">     
        <div class="button-container">
            <a href=""><i class="fa-brands fa-twitter"></i></a>
            <a href=""><i class="fa-brands fa-facebook"></i></a>
          </div>
        
        <div id="content">
            <h2 class="title">$name<span style="font-size: 20px; margin-left: 10px;">$time_blog<i style="margin-left: 10px;" class="fa-solid fa-hashtag">$tags</i></span></h2>
            <p>
            $content_with_line_breaks
             </p>
             <br>
        </div>
    </main>
    <script src="https://kit.fontawesome.com/.js" crossorigin="anonymous"></script>
</body>
</html>
HTML;
        // ブログの中身をファイルに保存
        file_put_contents($filename, $blogContent);

        // データベースに記事情報を保存
        $sql = "INSERT INTO posts (name, content, tags, filepath, date) VALUES ('$name', '$content', '$tags', '$filename', '$current_time')";
        if ($conn->query($sql) === TRUE) {
            echo "投稿が成功しました";
        } else {
            echo "エラー: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // パスワードが正しくない場合
        echo "エラー: パスワードが正しくありません";
    }
}

// 記事一覧を取得
$sql = "SELECT * FROM posts ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="copyright" content="Copyright &copy; 2024 user. All rights reserved." />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
</head>

<main id="main">
  <div id="content">
  <!-- 投稿フォーム（Admin Only） -->
  <?php if ($adminMode): ?>
    <h2>投稿フォーム（Admin Only）</h2>
    <form method="POST" action="index.php" enctype="multipart/form-data">
        名前: <input type="text" name="name"><br>
        内容: <textarea name="content"></textarea><br>
        タグ: <input type="text" name="tags"><br>
        画像アップロード: <input type="file" name="image[]" multiple><br>
        パスワード: <input type="password" name="password"><br>
        <input type="hidden" name="current_time" value="<?php echo date('Y年m月d日'); ?>">
        <input type="submit" value="投稿">
    </form>
    <br>
    <?php endif; ?>

<!-- ここから情報をいろいろ -->
    <h2>最近の投稿</h2>
    <ul class="styled-list">
<?php
$sql = "SELECT * FROM posts ORDER BY date DESC LIMIT 5";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
$url = $row['filepath'];
$title = $row['name'];
$date = date('Y年m月d日', strtotime($row['date']));
$category = $row['tags'];

echo '<li><a href="' . $url . '" style="color: #e8e6e3;">' . $title . ' <span style="color: #bcb6ad; font-size: 15px;">' . $date . ' <i class="fa-solid fa-hashtag">' . $category . '</i></span></a></li>';
}

?>
</ul>
<p class="kuu1"></p>
    <h2>#日記</h2>
    <ul class="styled-list">
    <?php
    // タグが "日記" の記事を取得
$sql = "SELECT * FROM posts WHERE tags = '日記' ORDER BY date DESC";
$result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $url = $row['filepath'];
        $title = $row['name'];
        $date = date('Y年m月d日', strtotime($row['date']));
        $category = $row['tags'];

        echo '<li><a href="' . $url . '" style="color: #e8e6e3;">' . $title . ' <span style="color: #bcb6ad; font-size: 15px;">' . $date . ' <i class="fa-solid fa-hashtag">' . $category . '</i></span></a></li>';
    }
?>
</ul>
<p class="kuu1"></p>
    <h2>#PC</h2>
    <ul class="styled-list">
    <?php
    // タグが "PC" の記事を取得
$sql = "SELECT * FROM posts WHERE tags = 'PC' ORDER BY date DESC";
$result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $url = $row['filepath'];
        $title = $row['name'];
        $date = date('Y年m月d日', strtotime($row['date']));
        $category = $row['tags'];

        echo '<li><a href="' . $url . '" style="color: #e8e6e3;">' . $title . ' <span style="color: #bcb6ad; font-size: 15px;">' . $date . ' <i class="fa-solid fa-hashtag">' . $category . '</i></span></a></li>';
    }
?>
</ul>
<p class="kuu1"></p>
    <h2>#ウイルス</h2>
    <ul class="styled-list">
    <?php
    // タグが "テスト" の記事を取得
$sql = "SELECT * FROM posts WHERE tags = 'ウイルス' ORDER BY date DESC";
$result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $url = $row['filepath'];
        $title = $row['name'];
        $date = date('Y年m月d日', strtotime($row['date']));
        $category = $row['tags'];

        echo '<li><a href="' . $url . '" style="color: #e8e6e3;">' . $title . ' <span style="color: #bcb6ad; font-size: 15px;">' . $date . ' <i class="fa-solid fa-hashtag">' . $category . '</i></span></a></li>';
    }
?>
</ul>
    <p class="kuu1"></p>
    <h2>一覧の投稿</h2>
    <ul class="styled-list">
    <?php
// 記事一覧を取得（最新の投稿が一番上に来るように）
$sql = "SELECT * FROM posts ORDER BY date DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
$url = $row['filepath']; 
$title = $row['name'];
$date = date('Y年m月d日', strtotime($row['date'])); 
$category = $row['tags'];

echo '<li><a href="' . $url . '" style="color: #e8e6e3;">' . $title . ' <span style="color: #bcb6ad; font-size: 15px;">' . $date . ' <i class="fa-solid fa-hashtag">' . $category . '</i></span></a></li>';
}

?>
</ul>

<br>
</div>
</main>

<script src="https://kit.fontawesome.com/.js" crossorigin="anonymous"></script>
</body>
</html>