<?php
session_start();

require_once('../config/app.php');

$isLogged = true;
if (empty($_SESSION['user'])) {
    header('location: /');
    exit;
}

// 全体的でmysql_xxx 系の関数を利用していますが、mysql_xxx系は非推奨です。
// mysqli_xxx 系を使うようにしましょう
$link = mysql_connect(DB_ADDR, DB_USER, DB_PASS);
if (!$link) {
    die('接続失敗です。'.mysql_error());
}

$db_selected = mysql_select_db(DB_NAME, $link);
if (!$db_selected){
    die('データベース選択失敗です。'.mysql_error());
}
mysql_set_charset('utf8');

$selectWord = '';
$word = '';
if (!empty($_GET['word'])) {
    $word = $_GET['word'];
    $selectWord = "AND b.name LIKE '%{$word}%'";
}

$sql =  "SELECT b.name,r.reserved,r.returned,u.name as user_name FROM books AS b ".
        "LEFT JOIN reserves AS r ON b.id=r.id ".
        "LEFT JOIN users AS u ON u.id=r.user_id ".
        "WHERE b.del_flg IS NULL AND r.del_flg IS NULL AND u.del_flg IS NULL AND ".
        "r.user_id='".$_SESSION['user']['id']."' {$selectWord} ".
        "ORDER BY reserved DESC";
$result = mysql_query($sql);
if (!$result) {
    die('クエリーが失敗しました。'.mysql_error());
}

$datas = [];
while ($tmp = mysql_fetch_assoc($result)) {
    if (!empty($tmp['name'])) {
        $datas[] = $tmp;
    }
}
mysql_close($link);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>借りた本一覧 - 脆弱図書館</title>
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
                    <li class="active"><a href="rent.php">借りた本一覧</a></li>
                    <?php endif;?>
                    <li><a href="contact.php">お問い合わせ</a></li>
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

        <div class="row">
            <form name="searchForm" id="searchFrom" method="get" action="" class="form-inner">
                <?php // ここでwordのサニタイズ（無害化）を行わないとXSS（クロスサイトスクリプティング）につながる ?>
                <input type="text" name="word" value="<?php echo $word;?>" class="" />
                <input type="submit" value="検索" class="btn btn-primary" />
            </form>
        </div>
        
        <table class="table table-striped table-bordered">
            <tr>
                <th>タイトル</th>
                <th>貸出日</th>
                <th>返却日</th>
                <th>借りた人</th>
            </tr>
            <?php foreach ($datas as $data) :?>
            <tr>
                <th><?php echo $data['name'];?></th>
                <th><?php echo $data['reserved'];?></th>
                <th><?php echo $data['returned'];?></th>
                <th><?php echo $data['user_name'];?></th>
            </tr>
            <?php endforeach;?>
        </table>
    
    </div> <!-- /container -->

</body>
</html>
