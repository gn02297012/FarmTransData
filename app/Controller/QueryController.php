<?php

App::uses('AppController', 'Controller');

class QueryController extends AppController {

    private $vegetables = array(
        '九層塔' => 1, '人參葉' => 1, '大心菜' => 1, '大蒜' => 1, '小白菜' => 1,
        '巴西利' => 1, '巴西蘑菇' => 1, '巴西蘑菇其他' => 1, '巴西蘑菇盒裝' => 1, '毛豆' => 1,
        '水蓮' => 1, '牛蒡' => 1, '冬瓜' => 1, '包心白' => 1, '半天花' => 1,
        '半天筍' => 1, '玉米' => 1, '甘蔗筍' => 1, '甘薯' => 1, '甘薯葉' => 1,
        '甘藍' => 1, '石蓮花' => 1, '朴菜' => 1, '百合' => 1, '百果' => 1,
        '竹筍' => 1, '西洋菜' => 1, '杏鮑菇' => 1, '秀珍菇' => 1, '芋' => 1,
        '豆薯' => 1, '其他' => 1, '其他花類' => 1, '其他菇類' => 1, '松茸' => 1,
        '油菜' => 1, '芥菜' => 1, '芥藍菜' => 1, '芫荽' => 1, '花豆' => 1,
        '花胡瓜' => 1, '花椰菜' => 1, '芹菜' => 1, '芽菜類' => 1, '虎豆' => 1,
        '金針花' => 1, '金針筍' => 1, '金絲菇' => 1, '青江白菜大梗' => 1, '青江白菜小梗' => 1,
        '青江白菜水耕' => 1, '青花苔' => 1, '青蔥' => 1, '南瓜' => 1, '扁蒲' => 1,
        '柳松菇' => 1, '洋菇' => 1, '洋蔥' => 1, '洛神花' => 1, '珊瑚菇' => 1,
        '珍珠菜' => 1, '皇宮菜' => 1, '紅鳳菜' => 1, '胡瓜' => 1, '胡蘿蔔' => 1,
        '苦瓜' => 1, '茄子' => 1, '韭菜' => 1, '香椿' => 1, '海菜' => 1,
        '茭白筍' => 1, '茴香' => 1, '茼蒿' => 1, '草石蠶' => 1, '草菇' => 1,
        '隼人瓜' => 1, '馬鈴薯' => 1, '敏豆' => 1, '晚香玉筍' => 1, '桶筍' => 1,
        '球莖甘藍' => 1, '甜椒' => 1, '荸薺' => 1, '莧菜' => 1, '雪里紅' => 1,
        '猴頭菇' => 1, '番茄' => 1, '筍片' => 1, '筍茸' => 1, '筍乾' => 1,
        '筍絲' => 1, '絲瓜' => 1, '菊芋' => 1, '菜豆' => 1, '菠菜' => 1,
        '菱角' => 1, '菾菜' => 1, '萊豆' => 1, '越瓜' => 1, '黃秋葵' => 1,
        '黑甜仔菜' => 1, '塌棵菜' => 1, '慈菇' => 1, '萵苣莖' => 1, '萵苣菜' => 1,
        '落花生' => 1, '榨菜' => 1, '辣椒' => 1, '樊花' => 1, '熟筍' => 1,
        '蓮藕' => 1, '豌豆' => 1, '豬母菜' => 1, '醃瓜' => 1, '蕎頭' => 1,
        '蕨菜' => 1, '濕木耳' => 1, '濕香菇' => 1, '蕹菜' => 1, '薑' => 1,
        '薯蕷' => 1, '鴻禧菇' => 1, '薺菜' => 1, '瓊花' => 1, '藤川七' => 1,
        '鵲豆' => 1, '蘆筍' => 1, '蠔菇' => 1, '鹹菜' => 1, '蘿蔔' => 1,
        '蘿蔔乾' => 1, '蠶豆' => 1,
    );
    private $fruits = array(
        '小番茄' => 1, '山竹' => 1, '木瓜' => 1, '水蜜桃' => 1, '火龍果' => 1,
        '甘蔗' => 1, '石榴' => 1, '百香果' => 1, '西瓜' => 1, '佛利檬' => 1,
        '李' => 1, '芒果' => 1, '其他' => 1, '奇異果' => 1, '枇杷' => 1,
        '波蘿蜜' => 1, '虎頭柑' => 1, '柚子' => 1, '柿子' => 1, '洋香瓜' => 1,
        '紅毛丹' => 1, '紅柑' => 1, '茂谷柑' => 1, '香瓜梨' => 1, '香蕉' => 1,
        '香櫞' => 1, '栗子' => 1, '桃子' => 1, '桑椹' => 1, '海梨柑' => 1,
        '草莓' => 1, '荔枝' => 1, '桶柑' => 1, '梅' => 1, '梨' => 1,
        '甜瓜' => 1, '甜桃' => 1, '甜橙' => 1, '蛋黃果' => 1, '棗子' => 1,
        '椪柑' => 1, '番石榴' => 1, '黃金果' => 1, '椰子' => 1, '楊桃' => 1,
        '楊梅' => 1, '溫州柑' => 1, '葡萄' => 1, '葡萄柚' => 1, '葡萄無子進口' => 1,
        '酪梨' => 1, '榴槤' => 1, '蜜棗' => 1, '鳳梨' => 1, '蓮霧' => 1,
        '橄欖' => 1, '龍眼' => 1, '藍莓' => 1, '雜柑' => 1, '蘋果' => 1,
        '蘋果五爪進口' => 1, '蘋果其他進口' => 1, '蘋果金冠進口' => 1, '蘋果秋香進口' => 1, '蘋果紅玉進口' => 1,
        '蘋果陸奧進口' => 1, '蘋果富士進口' => 1, '蘋果惠' => 1, '釋迦' => 1, '櫻桃' => 1,
        '豔陽柑' => 1,
    );

    public function beforeFilter() {
        parent::beforeFilter();
        //將此Controller下的所有Action改成同一個view
        $this->view = 'index';
        $this->layout = 'ajax';
        $this->autoRender = false;
    }

    /**
     * 取得查詢API的參數
     * @return type
     */
    private function getQueryParams() {
        //產生目前日期的字串(民國年)
        $date = new DateTime("now");
        $date->modify("-1911 year");
        $strNow = ltrim($date->format('Y.m.d'), '0');
        //設定預設值
        $params = array(
            '$top' => 100,
            '$skip' => 0,
            'StartDate' => $strNow,
            'EndDate' => $strNow,
            'Crop' => '',
            'Market' => '',
            'Category' => 0,
        );
        //檢查是否有query參數
        foreach ($params as $key => $value) {
            if (isset($this->request->query[$key])) {
                $params[$key] = $this->request->query[$key];
            }
        }
        return $params;
    }

    private function processPartitionData($data, $category = 0) {
        //定義有哪些種類
        $categorys = array('', 'vegetables', 'fruits');
        //例外處理
        if (!isset($categorys[$category])) {
            $category = 0;
        }
        $result = array();
        $keymap = array();
        $namemap = array();
        foreach ($data as $item) {
            //取出作物名稱中的名稱，把品種分離
            $pos = strpos($item['作物名稱'], '-');
            if ($pos === false) {
                $cat = $item['作物名稱'];
                //$result[$cat] = array();
            } else {
                $cat = substr($item['作物名稱'], 0, $pos);
            }
            //檢查是否為蔬菜，只保留蔬菜，其餘都剃除
            if ($category != 0 and ! isset($this->{$categorys[$category]}[$cat])) {
                continue;
            }
            //把名稱存到表裡面
            if (!isset($keymap[$cat])) {
                $result[] = array('name' => $cat, 'children' => array());
                $keymap[$cat] = (count($result) - 1);
            }
            if (!isset($namemap[$item['作物代號']])) {
                $result[$keymap[$cat]]['children'][] = array(
                    'date' => $item['交易日期'],
                    'code' => $item['作物代號'],
                    'name' => $item['作物名稱'],
                    'marketCode' => $item['市場代號'],
                    'market' => $item['市場名稱'],
                    'priceTop' => $item['上價'],
                    'priceMid' => $item['中價'],
                    'priceBottom' => $item['下價'],
                    'price' => $item['平均價'],
                    'quantity' => $item['交易量'],
                    'amount' => $item['平均價'] * $item['交易量'],
                    'marketCount' => 1,
                );
                $namemap[$item['作物代號']] = (count($result[$keymap[$cat]]['children']) - 1);
            } else {
                $subkey = $namemap[$item['作物代號']];
                $tmp = $result[$keymap[$cat]]['children'][$subkey];
                $tmp['quantity'] += $item['交易量'];
                $tmp['amount'] += $item['平均價'] * $item['交易量'];
                $tmp['price'] = $tmp['amount'] / $tmp['quantity'];
                $tmp['marketCount']++;
                $result[$keymap[$cat]]['children'][$subkey] = $tmp;
            }
        }
        return json_encode(array('name' => 'return', 'children' => $result));
    }

    private function processLineData($data) {
        $result = array();
        $keymap = array();
        foreach ($data as $item) {
            $key = $item['作物代號'] . $item['交易日期'];
            if (!isset($keymap[$key])) {
                $result[] = array(
                    'date' => $item['交易日期'],
                    'code' => $item['作物代號'],
                    'name' => $item['作物名稱'],
                    'marketCode' => $item['市場代號'],
                    'market' => $item['市場名稱'],
                    'priceTop' => $item['上價'],
                    'priceMid' => $item['中價'],
                    'priceBottom' => $item['下價'],
                    'price' => $item['平均價'],
                    'quantity' => $item['交易量'],
                    'amount' => $item['平均價'] * $item['交易量'],
                    'marketCount' => 1,
                );
                $keymap[$key] = (count($result) - 1);
            } else {
                $tmp = $result[$keymap[$key]];
                $tmp['quantity'] += $item['交易量'];
                $tmp['amount'] += $item['平均價'] * $item['交易量'];
                $tmp['price'] = $tmp['amount'] / $tmp['quantity'];
                $tmp['marketCount']++;
                $result[$keymap[$key]] = $tmp;
            }
        }
        return json_encode($result);
    }

    private function callAPI($params) {
        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        $params = $this->getQueryParams();
        $HttpSocket->get('http://m.coa.gov.tw/OpenData/FarmTransData.aspx', $params);
        $data = json_decode($HttpSocket->response['body'], true);
        return $data;
    }

    public function partition() {
        $params = $this->getQueryParams();
        $data = $this->callAPI($params);
        $result = $this->processPartitionData($data, $params['Category']);
        echo $result;
    }

    public function line() {
        $params = $this->getQueryParams();
        $data = $this->callAPI($params);
        $result = $this->processLineData($data);
        echo $result;
    }

}
