<?php

App::uses('AppController', 'Controller');

class QueryController extends AppController {

    public $uses = array('Query');

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
            if ($category != 0 and !isset($this->Query->{$categorys[$category]}[$cat])) {
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
                $tmp['marketCount'] ++;
                $result[$keymap[$cat]]['children'][$subkey] = $tmp;
            }
        }
        return json_encode(array('name' => 'return', 'children' => $result));
    }

    private function processLineData($data) {
        $result = array();
        $keymap = array();
        foreach ($data as &$item) {
            $key = $item['作物代號'] . $item['交易日期'];
            if (!isset($keymap[$key])) {
                $result[] = array(
                    'date' => $item['交易日期'],
                    'code' => $item['作物代號'],
                    'name' => $item['作物名稱'],
                    'marketCode' => $item['市場代號'],
                    'market' => $item['市場名稱'],
                    'priceTop' => (double) $item['上價'],
                    'priceMid' => (double) $item['中價'],
                    'priceBottom' => (double) $item['下價'],
                    'price' => (double) $item['平均價'],
                    'quantity' => (double) $item['交易量'],
                    'amount' => (double) $item['平均價'] * $item['交易量'],
                    'marketCount' => 1,
                );
                $keymap[$key] = (count($result) - 1);
            } else {
                $tmp = &$result[$keymap[$key]];
                $tmp['quantity'] += $item['交易量'];
                $tmp['amount'] += $item['平均價'] * $item['交易量'];
                $tmp['price'] = round($tmp['amount'] / $tmp['quantity'], 2);
                $tmp['marketCount'] ++;
                //$result[$keymap[$key]] = $tmp;
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

    public function getNames($cat) {
        $arr = array('vegetables', 'fruits');
        $result = array();
        if (array_search($cat, $arr) !== false) {
            $result = $this->Query->$cat;
        }
        return json_encode($result);
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