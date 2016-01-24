<?php
use yii\helpers\Html;
/* @var $this \yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= _include_once("@common/views/meta.php"); ?>
    <?= _include_once("@common/views/meta.php"); ?>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <?=_asset('@static/css/base.min.css'); ?>
    <script type="text/javascript">
        <?= _include("@webroot/js/mysoft.js");?>
    </script>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap">
    <div class="container">
        <?= $content ?>
    </div>
</div>
<?=_asset('@static/js/base.min.js?v=aaa'); ?>
<?php $this->endBody() ?>
<script type="text/javascript">
    var tplData = <?=json_encode($this->params['tpldata']);?>||{};
    for(var i = 0;i<mysoft.initfunc.length;i++){mysoft.initfunc[i]();}
</script>
</body>
</html>
<?php $this->endPage() ?>
