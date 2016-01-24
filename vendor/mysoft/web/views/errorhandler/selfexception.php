<?php
/* @var $exception \yii\web\HttpException|\Exception */
/* @var $handler \yii\web\ErrorHandler */
if ($exception instanceof \yii\web\HttpException) {
    $code = $exception->statusCode;
} else {
    $code = $exception->getCode();
}
$name = $handler->getExceptionName($exception);
if ($name === null) {
    $name = 'Error';
}
if ($code) {
    $name .= " (#$code)";
}

//if ($exception instanceof \yii\base\UserException) {
//    $message = $exception->getMessage();
//} else {
//    $message = 'An internal server error occurred.';
//}

$message = $exception->getMessage();

if (method_exists($this, 'beginPage')) {
    $this->beginPage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="format-detection" content="telephone=no"/>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <title>温馨提示</title>
    <style>
        html,body {
            padding:0;
            margin:0;
            font: 12px/1.5 "Microsoft YaHei",Helvetica, Arial, Verdana,sans-serif;
            height: 100%;

        }
        .out-wrapper {
            height: 100%;
            display: table;
            margin: 0 auto;
        }
        .container {
            display: table-cell;
            vertical-align: middle;
        }
        .container {
            text-align: center;
        }
        .icon-container {
            text-align: center;
            line-height: 0;
        }
        .icon_unauthorize {
            width: 84px;
            height: 84px;
        }
        .prompt-title {
            font-size: 18px;
            color: #666;
            line-height: 30px;
            margin-top: 24px;
        }
        .contact-title {
            font-size: 15px;
            color: #adadad;
            line-height: 27px;
            padding-bottom: 17px;
        }
        
        .tel a {
            text-decoration: none;
            color: #3485e9;
        }
        .tel {
            font-size: 18px;
            line-height: 45px;
            border-width: 1px 0;
        }

    </style>
</head>
<body>
    <div class="out-wrapper">
        <div class="container">
            <div class="icon-container">
                <img src="http://static-ci.mysoft.com.cn/images/404.jpg"/>
            </div>
            <div class="prompt-title">
              <?= nl2br($handler->htmlEncode($message)) ?>
            </div>
            <div class="contact">
                <div class="contact-title">请联系系统管理员</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
if (method_exists($this, 'endPage')) {
    $this->endPage();
}