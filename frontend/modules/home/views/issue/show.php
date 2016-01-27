<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title>我的任务</title>
    <?= _asset('@web/AmazeUI/assets/css/amazeui.min.css'); ?>
    <?= _asset('@web/AmazeUI/assets/css/amaze.min.css'); ?>
</head>
<body>
<form class="am-form">
    <fieldset>
        <div class="am-form-group">
            <label for="doc-ipt-email-1">主题</label>
            <input type="text" class="" id="doc-ipt-email-1" placeholder="" value="<?=$tpData['summary']?>">
        </div>

        <div class="am-form-group">
            <label for="doc-ipt-pwd-1">密码</label>
            <input type="text" class="" id="doc-ipt-pwd-1" placeholder="设置个密码吧">
        </div>

        <p>
            <button type="submit" class="am-btn am-btn-default">提交</button>
        </p>
    </fieldset>
</form>
<script type="text/javascript">
    var tplData = <?=json_encode($tpData,true);?>||{};
</script>