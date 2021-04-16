<?php
require '../vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\ObjectUploader;
use Aws\Credentials\CredentialProvider;

require_once('../config/app.php');
session_start();

if (empty($_SESSION['user'])) {
    echo 'アクセス権がありません';
    exit;
}

$ini = '../config/credential.ini';
$iniProvider = CredentialProvider::ini('AWS', $ini);
$iniProvider = CredentialProvider::memoize($iniProvider);

$s3Client = new S3Client([
    'region' => 'ap-northeast-1',
    'version' => '2006-03-01',
    'credentials' => $iniProvider,
]);

$bucket = BACKET_NAME;


if (!empty($_FILES)) {
    $key = date('YmdHis').'.jpg';

    $file = $_FILES['file']['tmp_name'];
    if (!is_uploaded_file($file)) {
        echo 'ファイルのナップロードに失敗しました';
        exit;
    }

    $source = fopen($file, 'rb');

    $uploader = new ObjectUploader(
        $s3Client,
        $bucket,
        $key,
        $source
    );

    try {
        $result = $uploader->upload();
        if ($result["@metadata"]["statusCode"] == '200') {
            header('Location: /fileupload.php?status=success');
        }
    } catch (AwsException $e) {
    }

}
else {
    // S3バケットの画像を全て取得
    $objects = $s3Client->listObjects(array(
        'Bucket' => $bucket
    ));
}


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ファイルアップロード - 脆弱図書館</title>
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
                    <li><a href="rent.php">借りた本一覧</a></li>
                    <li class="active"><a href="contact.php">お問い合わせ</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="active"><a href="logout.php">ログアウト <span class="sr-only">(current)</span></a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
    
    
    <div class="container">
        <h1>ファイルアップロード</h1>

        <?php if (!empty($_GET['status'])):?>
        <div class="alert alert-success">
            ファイルのアップロードに成功しました
        </div>
        <?php endif;?>
        <form name="contactForm" id="contactForm" action="" method="post" enctype="multipart/form-data">
            <?php // 本来はcsrf_token をhiddenパラメータに設定して送信したりする csrf対策 ?>
            <table class="table table-bordered table-striped">
                <tr>
                    <th>ファイルアップロード</th>
                    <td>
                        <input type="file" name="file" accept="image/jpeg" required />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">
                        <input type="submit" value="送信" class="btn btn-primary" />
                    </td>
                </tr>
            </table>
        </form>

        <?php if (!empty($objects)):?>
            <?php foreach ($objects['Contents'] as $object) :?>
                <img width="300" src="https://s3-ap-northeast-1.amazonaws.com/<?php echo $bucket ?>/<?php echo $object['Key'] ?>" /><br />
            <?php endforeach; ?>
        <?php endif; ?>
    </div> <!-- /container -->

</body>
</html>
