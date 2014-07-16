<?php

App::uses('AppController', 'Controller');

class DiagramsController extends AppController {

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
            '$top' => 10,
            '$skip' => 0,
            'StartDate' => $strNow,
            'EndDate' => $strNow,
            'Crop' => '',
            'Market' => '');
        //檢查是否有query參數
        foreach ($params as $key => $value) {
            if (isset($this->request->query[$key])) {
                $params[$key] = $this->request->query[$key];
            }
        }
        return $params;
    }

    private function processJsonData($data) {
        $result = array();
        $keymap = array();
        foreach ($data as $item) {
            $pos = strpos($item['作物名稱'], '-');
            if ($pos === false) {
                $cat = $item['作物名稱'];
                //$result[$cat] = array();
            } else {
                $cat = substr($item['作物名稱'], 0, $pos);
            }
            if (!isset($keymap[$cat])) {
                $result[] = array('name' => $cat, 'children' => array());
                $keymap[$cat] = (count($result) - 1);
            }
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
                'quantity' => $item['交易量'],);
        }
        return json_encode(array('name' => 'data', 'children' => $result));
    }

    public function index() {
        
    }

    public function query() {
        $this->layout = 'ajax';
        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        $HttpSocket->get('http://m.coa.gov.tw/OpenData/FarmTransData.aspx', $this->getQueryParams());
        $data = json_decode($HttpSocket->response['body'], true);
        $result = $this->processJsonData($data);
        echo $result;
    }

}
