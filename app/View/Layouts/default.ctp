<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>農產品交易行情資料視覺化</title>
        <?php
        echo $this->Html->meta('icon');

        //echo $this->Html->css('cake.generic');

        echo $this->fetch('meta');
        //echo $this->fetch('css');
        echo $this->fetch('script');
        ?>
<style>

body {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  margin: auto;
  position: relative;
  width: 960px;
  min-height: 900px;
}

form {
  position: absolute;
  right: 10px;
  top: 10px;
}

</style>
    </head>
    <body>
        <?php echo $this->Session->flash(); ?>

        <?php echo $this->fetch('content'); ?>
        <?php //echo $this->element('sql_dump'); ?>
    </body>
</html>
