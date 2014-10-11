<script>
    //設定ControlPanelCtrl的參數     
    $(document).ready(function() {
        angular.element('.controlPanel').scope().$apply(function($scope, $http) {
            $scope.showControlPanel = false;
        });
    });
</script>

<div class="row">
    <div class="landingpage">
        <div class="container">
            <div class="col-sm-4 col-sm-offset-8">
                <div style="max-width: 313px;">
                    <div class="arrow_box" style="font-size: 2em;">
                        農價資訊站
                    </div>
                    <span class="title">KnowMe</span>
                </div>

                <br />
                <br />
                <p>想要知道菜價上漲還下跌? 滑鼠點一點就行了!</p>
                <br />
                <br />
            </div>
        </div>
            <div class="arrowDown">
                <a href="#" onclick="scrollToFooter()">
                    <span class="glyphicon glyphicon-chevron-down"></span>
                </a>                
            </div>
    </div>   
</div>
<div class="row">
    <br /><br />
    <div class="col-sm-4 col-md-4">
        <div class="thumbnail features">
            <div class="features-icon"><span class="glyphicon glyphicon-eye-open"></span></div>
            <div class="caption">
                <h3>視覺化</h3>
                <p>使用<a href="http://d3js.org/" target="_blank">D3.js</a>進行資料視覺化，不用再花費眼力看死板板的文字，各種圖表一目瞭然。</p>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-4">
        <div class="thumbnail features">
            <div class="features-icon"><span class="glyphicon glyphicon-thumbs-up"></span></div>
            <div class="caption">
                <h3>簡單使用</h3>
                <p>改善<a href="http://amis.afa.gov.tw/default.asp" target="_blank">農產品交易行情站</a>難以操作的問題，提供新世代網頁的操作方式。</p>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-4">
        <div class="thumbnail features">
            <div class="features-icon"><span class="glyphicon glyphicon-search"></span></div>
            <div class="caption">
                <h3>資訊豐富</h3>
                <p>提供了多種圖表呈現的方法，節省在多個頁面中交叉查詢的時間。</p>
            </div>
        </div>
    </div>
</div>

<div class="row" style="background-color: #f0f0f0;height: 300px;padding-top: 50px;">
    <div class="col-sm-4 col-md-10 col-md-offset-1" style="">
        <h2>關於</h2>
        <div>雖然現今農委會有提供農產品交易行情價的網站供人查詢，但是其操作不易，資訊模糊不明，於是我們建立這個平台，除了能夠讓農民知道各作物在拍賣市場的最新行情價，也可以透過我們的統計圖表進行分析，可以讓農民將農產品批給上游時，擁有議價的能力。</div>
        <br />
    </div>
</div>

<div class="row">
    <br /><br />
    <div class="col-sm-3">
        <div class="thumbnail features">
            <div class="caption">
                <a href="<?php echo $this->Html->webroot('diagrams/line') ?>">  
                    <h3>價格走勢圖</h3>
                </a>
                <p>提供了多種圖表呈現的方法，節省在多個頁面中交叉查詢的時間。</p>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="thumbnail features">
            <div class="caption">
                <a href="<?php echo $this->Html->webroot('diagrams/dashboard') ?>">   
                    <h3>市場分析圖</h3>
                </a>
                <p>提供了多種圖表呈現的方法，節省在多個頁面中交叉查詢的時間。</p>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="thumbnail features">
            <div class="caption">
                <a href="<?php echo $this->Html->webroot('diagrams/bubble') ?>">     
                    <h3>價量走勢圖</h3>
                </a>
                <p>提供了多種圖表呈現的方法，節省在多個頁面中交叉查詢的時間。</p>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="thumbnail features">
            <div class="caption">
                <a href="<?php echo $this->Html->webroot('diagrams/rank') ?>">
                    <h3>交易量排行榜</h3>
                </a>
                <p>提供了多種圖表呈現的方法，節省在多個頁面中交叉查詢的時間。</p>
            </div>
        </div>
    </div>
</div>


<div class="row" style="background-color: #f0f0f0;">
    <div class="col-sm-12 text-center" style="">
        <br />
        Copyright &copy;  廖建銘、吳妮易 2014
        <br />
    </div>
</div>