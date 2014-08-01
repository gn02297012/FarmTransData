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
            'angular.min',
            'd3.v3.min',
            'bootstrap-tooltip',
            'bootstrap.min',
        ));
        ?>
        <style>
            body {
                /*                margin: auto;
                                position: relative;
                                width: 960px;
                                min-height: 900px;*/
            }

            .controlForm {
                position: absolute;
                right: 10px;
                top: 10px;
            }
        </style>
        <script>
            /**
             * 將日期轉換成民國年.月.日的字串
             * @param {date} d 日期
             * @returns {String} 民國年.月.日
             */
            var formatROCDate = function(d) {
                d = new Date(d);
                d.setFullYear(d.getFullYear() - 1911);
                var mm = d.getMonth() + 1,
                        dd = d.getDate();
                if (mm < 10) {
                    mm = '0' + mm;
                }
                if (dd < 10) {
                    dd = '0' + dd;
                }
                return (d.getFullYear()) + '.' + mm + '.' + dd;
            };
        </script>
    </head>
    <body ng-app>
        <?php echo $this->Session->flash(); ?>

        <?php echo $this->fetch('content'); ?>
        <?php //echo $this->element('sql_dump'); ?>
    </body>
</html>
