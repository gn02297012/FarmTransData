//控制介面的controller
function ControlPanelCtrl($scope, $http) {
//是否顯示選單，全部作物與市場
    $scope.showAllCrop = true;
    $scope.showMarket = true;
    //API參數
    $scope.baseUrl = '';
    $scope.Crop = '';
    $scope.Market = '';
    $scope.StartDate = formatDateInput(new Date(), 86400 * 1000 * 10);
    $scope.EndDate = formatDateInput(new Date());
    $scope.top = 500;
    $scope.skip = 0;
    //=====以上為不同圖表的參數=====

    //作物名稱選單
    $scope.categorys = [];
    $scope.items = null;
    //市場選單
    $scope.markets = [];
    //是否要將查詢按鈕變成等待中
    $scope.btnSubmitWaiting = false;

    //動態載入作物名稱與市場清單
    $http.get(webroot + 'query/getCropAndMarketList').success(function(data) {
        //設定作物名稱清單
        $scope.categorys = data['crop'];
        //$scope.selCat = $scope.categorys[($scope.showAllCrop ? 0 : 1)];
        $scope.selCat = $scope.categorys[1]; //預設不要是全部
        $scope.items = $scope.selCat.items;
        if ($scope.showAllCrop) {
            $scope.items.splice(0, 0, '全部');
        }
        $scope.Crop = $scope.items[0];
        //設定市場清單
        $scope.markets = data['market'];
        $scope.Market = $scope.markets[0];
    });
    //更新作物選單
    $scope.update = function(selectedCat, Crop) {
        $scope.items = selectedCat.items;
        if ($scope.showAllCrop && $scope.items[0] != '全部') {
            $scope.items.splice(0, 0, '全部');
        }
        $scope.Crop = Crop ? Crop : $scope.items[0];
    };
    //送出查詢
    $scope.submit = function() {
    };
    //負責處理GET的函數，由於全部寫在submit會出錯，所以才多透過這個
    $scope.getData = function(url, callback, sourcePathName) {
        $scope.btnSubmitWaiting = true;
        console.log(encodeURI(url));
        $http.get(url).success(function(data) {
            $scope.btnSubmitWaiting = false;
            //如果網址中的pathname改變，就捨棄本次的資料
            if (sourcePathName !== window.location.pathname) {
                return;
            }
            callback(data);
        }).error(function() {
            $scope.btnSubmitWaiting = false;
        });
        //$('#controlPanelBody').collapse('hide');
    };

    //按鈕等待
    $scope.btnSubmitSwitch = function(show) {
        var currStat = angular.element('#submit').attr('disabled');
        if (show === undefined) {
            show = !currStat;
        }
        if (show) {

        }
    };

    $scope.settings = {};
    //新增設定
    $scope.addSetting = function($event) {
        var s = {CatName: $scope.selCat.name, Category: $scope.selCat.cat, Crop: $scope.Crop, Market: $scope.Market, t: (new Date()).getTime()};
        $scope.settings[s.t] = s;
        $scope.saveSetting();
        $('#settingList').collapse('show');
        $event.stopPropagation();
    };
    //儲存設定
    $scope.saveSetting = function() {
        var obj = {setting: $scope.settings};
        localStorage.setItem('setting', JSON.stringify(obj));
    };
    //載入設定
    $scope.loadSetting = function() {
        var old = localStorage.getItem('setting');
        if (old !== undefined && old !== null && typeof old === "string") {
            old = JSON.parse(old);
        } else {
            old = {setting: {}};
        }
        $scope.settings = old.setting;
    };
    //設定設定檔到控制面板
    $scope.setSetting = function(d) {
        if ($scope.selCat.cat !== d.Cateogry) {
            $scope.selCat = $scope.categorys[d.Category];
        }
        $scope.update($scope.categorys[d.Category], d.Crop);
        $scope.Market = d.Market;
    }

    //刪除
    $scope.deleteSetting = function(t) {
        delete $scope.settings[t];
        $scope.saveSetting();
    }

    //是否在作物清單中顯示"全部"這個選項
    $scope.$watch('showAllCrop', function(newValue, oldValue) {
        if (newValue === oldValue) {
            return;
        }
        if ($scope.showAllCrop) {
            $('[ng-model="selCat"] option:first-child').show();
            if ($scope.items && $scope.items[0] != '全部') {
                $scope.items.splice(0, 0, '全部');
            }
        } else {
            $('[ng-model="selCat"] option:first-child').hide();
            //如果主分類選到全部，就自動切換到下一個
            if ($scope.selCat && $scope.selCat.cat == 0) {
                $scope.selCat = $scope.categorys[1];
                $scope.items = $scope.selCat['items'];
                $scope.Crop = $scope.items[0];
            }
            //如果第二層選到全部，移除該選項並切換到下一個
            if ($scope.selCat && $scope.items[0] === '全部') {
                $scope.selCat['items'] = $scope.selCat['items'].slice(1);
                $scope.items = $scope.selCat['items'];
                $scope.Crop = $scope.items[0];
            }
        }
    });
}

//價格圖中的日期選擇的controller
function DatePickerCtrl($scope) {
    $scope.domain = [0, 0];
    $scope.range = [0, 0];
    $scope.startDate = $scope.range[0];
    $scope.endDate = $scope.range[1];
    $scope.selectedDate = 0;
    $scope.init = function(domain) {
        $scope.domain = [domain[0].getTime(), domain[1].getTime()];
    };
    $scope.$watch('domain', function(newValue, oldValue) {
        if (newValue === undefined) {
            return;
        }
        var total = (newValue[1] - newValue[0]) / 86400 / 1000;
        var range = [0, total];
        $scope.range = range;
        $scope.startDate = $scope.range[0];
        $scope.endDate = $scope.range[1];
        $scope.selectedDate = 0;
    }, true);
    $scope.$watch('selectedDate', function(newValue, oldValue) {
        if (newValue === oldValue) {
            return;
        }
        //計算滑鼠移動到的資料時間
        var t = $scope.domain[0] + $scope.selectedDate * 86400000;
        //重畫掃描線
        moveScanline(x(t));
        //更新詳細資料的表格內容
        updateDetailTable(t);
    }, true);
}