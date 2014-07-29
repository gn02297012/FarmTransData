<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>農產品交易行情資料視覺化</title>
        <?php
        echo $this->Html->meta('icon');
        echo $this->fetch('meta');
        echo $this->Html->css(array(
            'bootstrap.min',
        ));
        echo $this->Html->script(array('jquery-1.10.2'));
        echo $this->Html->script(array(
            'd3.v3.min',
            'bootstrap-tooltip',
            'bootstrap.min',
        ));
        ?>
        <style>
            body {
                margin: auto;
                position: relative;
                width: 960px;
                min-height: 900px;
            }

            .controlForm {
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
