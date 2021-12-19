<?php

require_once('../config/app.php');

session_start();

$entryFlg = false;
if (!empty($_REQUEST)) {
    
    $subject = $_REQUEST['subject'];
    $email = $_REQUEST['email'];
    $message = $_REQUEST['message'];
    
    // 上記変数の validate 必須！！
    
    
    // csrfトークンを前ページで発行し、ここで検証を行わない場合、
    // このページを直接叩かれるとお問い合わせ処理が行われてしまう。
    // そのためSNS などで http://xxxxx/contact_entry.php?subject=◯ね&email=hogehoge@hogehogehoge.hoge&message=さっさと○ね
    // などというリンクを短縮URLに変換し、友達や知らない人に踏ませることで
    // 脅迫やいたずらが行われてしまう危険性がある
    
    // 全体的でmysql_xxx 系の関数を利用していますが、mysql_xxx系は非推奨です。
    // mysqli_xxx 系を使うようにしましょう
    $link = mysqli_connect(DB_ADDR, DB_USER, DB_PASS);
    if (!$link) {
        die('接続失敗です');
    }
    
    $db_selected = mysqli_select_db($link, DB_NAME);
    if (!$db_selected){
        die('データベース選択失敗です。'.mysqli_error($link));
    }
    mysqli_set_charset($link, 'utf8');
    
    $sql =  "INSERT INTO contacts(subject, email, message) ".
            "VALUES('{$subject}', '{$email}', '{$message}')";
    $result = mysqli_query($link, $sql);
    if (!$result) {
        die('クエリーが失敗しました。'.mysqli_error($link));
    }
    
    $entryFlg = true;
    
    mysqli_close($link);
}

if ($entryFlg == false) {
    header('location: ./');
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>お問い合わせ完了 - 脆弱図書館</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Static navbar -->
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">脆弱図書館</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="./">トップ</a></li>
                    <li><a href="search.php">検索</a></li>
                    <?php if ($isLogged):?>
                        <li><a href="rent.php?id=<?php echo $_SESSION['user']['id'];?>">借りた本一覧</a></li>
                    <?php endif;?>
                    <li class="active"><a href="contact.php">お問い合わせ</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php if ($isLogged):?>
                        <li class="active"><a href="logout.php">ログアウト <span class="sr-only">(current)</span></a></li>
                        <?php else:?>
                        <li><a href="login.php">ログイン</a></li>
                    <?php endif;?>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
    
    
    <div class="container">
        <h1>お問い合わせ完了</h1>
        <div class="row">
            <p>お問い合わせありがとうございました。</p>
            <p>
                担当のものから入力いただいたメールアドレス宛に折り返し連絡差し上げますので少々お待ちください。
            </p>
        </div>
    </div> <!-- /container -->

</body>
</html>
