<?= _include("@backend/views/nav.php"); ?>
<?php
_asset('@web/demo/css/site.css');
?>
hello word
<?= _include("@backend/views/footer.php"); ?>
<script type="text/javascript">
    mysoft.prepare(function(){
        console.log($("body"));
    });
</script>