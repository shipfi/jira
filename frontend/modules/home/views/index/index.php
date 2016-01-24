<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>明源云产品研发管理平台</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <?= _asset('@web/AmazeUI/assets/css/amazeui.min.css'); ?>
    <?= _asset('@web/js/base_new.min.js'); ?>
    <?= _asset('@web/js/mysoft.js'); ?>
    <?= _include_once("@common/components/dialog/index.php") ?>
    <style>
        .header {
            text-align: center;
        }

        .header h1 {
            font-size: 200%;
            color: #333;
            margin-top: 30px;
        }

        .header p {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="am-g">
        <h1><a href="/">JIRA</a></h1>
        <!--<p>欢迎访问<br/>明源云产品研发管理平台</p>-->
    </div>
    <hr/>
</div>
<div class="am-g">
    <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
        <form method="post" class="am-form">
            <label for="email">账号:</label>
            <input type="email" name="" id="email" value="">
            <br>
            <label for="password">密码:</label>
            <input type="password" name="" id="password" value="">
            <br>
            <label for="project-key">项目:</label>
            <input type="text" name="" id="project-key" value="">
            <br>
            <label for="remember-me">
                <input id="remember-me" type="checkbox">
                记住密码
            </label>
            <br/>

            <div class="am-cf">
                <input type="button" name="" value="登 录" class="am-btn am-btn-primary am-btn-sm am-fl" id="btn-login">
                <input type="button" name="" value="忘记密码 ^_^? " class="am-btn am-btn-default am-btn-sm am-fr">
            </div>
        </form>
        <hr>
        <p>© 2015 AllMobilize, Inc. Licensed under MIT license.</p>
    </div>
</div>
</body>
</html>
<script type="text/javascript">
    $("#btn-login").on("click", function () {
        var handData = {
            name: $("#email").val(),
            psw: $("#password").val(),
            projkey: $("#project-key").val(),
            rem: $("#remember-me").prop('checked') ? "1" : "0"
        };
        console.log(handData);
        $.ajax({
            url: "<?= U(['ajax-login'])?>",
            type: "post",
            data: handData,
            dataType: "json",
            success: function (result) {
                if (result.isSuccess == "1") {
                    console.log(result.message);
                    //跳转到住面板
                    window.location.href = result.message;
                } else {
                    $.toast(result.message);
                }
            }, error: function () {

            }
        });
    });
</script>