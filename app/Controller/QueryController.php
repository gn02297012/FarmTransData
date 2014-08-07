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

    private function &getBasicName($crop) {
        //取出作物名稱中的名稱，把品種分離
        $pos = strpos($crop, '-');
        if ($pos === false) {
            $cat = $crop;
        } else {
            $cat = substr($crop, 0, $pos);
        }
        return $cat;
    }

    private function processData($data, $params, &$result) {
        //$result = array();
        foreach ($data as &$item) {
            //把花卉的資料剔除
            if (strpos($item['市場名稱'], '市場') !== false) {
                continue;
            }
            //把不同品種的作物都剔除
            $cat = $this->getBasicName($item['作物名稱']);
            if (!empty($params['Crop']) and ( strcmp($params['Crop'], $cat) != 0)) {
                continue;
            }
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
            );
            if (count($result) >= $params['$top']) {
                break;
            }
        }
        //return json_encode($result);
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
            //把花卉的資料剔除
            if (strpos($item['市場名稱'], '市場') !== false) {
                continue;
            }
            //取出作物名稱中的名稱，把品種分離
            $pos = strpos($item['作物名稱'], '-');
            if ($pos === false) {
                $cat = $item['作物名稱'];
                //$result[$cat] = array();
            } else {
                $cat = substr($item['作物名稱'], 0, $pos);
            }
            //檢查是否為蔬菜，只保留蔬菜，其餘都剃除
            if ($category != 0 and ! isset($this->Query->{$categorys[$category]}[$cat])) {
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

    private function processLineData($data, $params) {
        $result = array();
        $keymap = array();
        foreach ($data as &$item) {
            //把花卉的資料剔除
            if (strpos($item['市場名稱'], '市場') !== false) {
                continue;
            }
            //取出作物名稱中的名稱，把品種分離
            $pos = strpos($item['作物名稱'], '-');
            if ($pos === false) {
                $cat = $item['作物名稱'];
                //$result[$cat] = array();
            } else {
                $cat = substr($item['作物名稱'], 0, $pos);
            }
            if (!empty($params['Crop']) and ( strcmp($params['Crop'], $cat) != 0)) {
                continue;
            }
            $key = $item['作物名稱'] . $item['交易日期'];
            if (!isset($keymap[$key])) {
                $keymap[$key] = count($result);
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
                    'amount' => (double) $item['平均價'] * (double) $item['交易量'],
                    'marketCount' => 1,
                );
            } else {
                $tmp = &$result[$keymap[$key]];
                $tmp['quantity'] += $item['交易量'];
                $tmp['amount'] += (double) $item['平均價'] * (double) $item['交易量'];
                $tmp['price'] = round($tmp['amount'] / $tmp['quantity'], 2);
                $tmp['marketCount'] ++;
                //$result[$keymap[$key]] = $tmp;
            }
        }
        return json_encode($result);
    }

    private function processDashBoardData($data, $params) {
        $result = array();
        $keymap = array();
        foreach ($data as &$item) {
            //把花卉的資料剔除
            if (strpos($item['市場名稱'], '市場') !== false) {
                continue;
            }
            //取出作物名稱中的名稱，把品種分離
            $pos = strpos($item['作物名稱'], '-');
            if ($pos === false) {
                $cat = $item['作物名稱'];
                //$result[$cat] = array();
            } else {
                $cat = substr($item['作物名稱'], 0, $pos);
            }
            if (!empty($params['Crop']) and ( strcmp($params['Crop'], $cat) != 0)) {
                continue;
            }
            $key = $item['作物名稱'];
            if (!isset($keymap[$key])) {
                $keymap[$key] = count($result);
                $result[] = array(
                    'date' => $item['交易日期'],
                    'code' => $item['作物代號'],
                    'name' => $item['作物名稱'],
                    'markets' => array(
                        $item['市場代號'] => array(
                            'market' => $item['市場名稱'],
                            'quantity' => (double) $item['交易量'],
                            'amount' => ((double) $item['平均價']) * ((double) $item['交易量']),
                        ),
                    )
                );
            } else {
                $tmp = &$result[$keymap[$key]];
                //$tmp['date'] = "{$item['交易日期']}-{$tmp['date']}";
                if (!isset($tmp['markets'][$item['市場代號']])) {
                    $tmp['markets'][$item['市場代號']] = array(
                        'market' => $item['市場名稱'],
                        'quantity' => 0,
                        'amount' => 0,
                    );
                }
                $tmp['markets'][$item['市場代號']]['quantity'] += $item['交易量'];
                $tmp['markets'][$item['市場代號']]['amount'] += $item['平均價'] * $item['交易量'];
                //$result[$keymap[$key]] = $tmp;
            }
        }
        return json_encode($result);
    }

    private function callAPI($params, $jsonDecode = true) {
        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket();
        empty($params) and $params = $this->getQueryParams();
        $HttpSocket->get('http://m.coa.gov.tw/OpenData/FarmTransData.aspx', $params);
        if ($jsonDecode) {
            $data = json_decode($HttpSocket->response['body'], true);
        } else {
            $data = $HttpSocket->response['body'];
        }
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

    public function search() {
        $params = $this->getQueryParams();
        $result = array();
        while (1) {
            $data = $this->callAPI($params);
            if (empty($data)) {
                break;
            }
            $this->processData($data, $params, $result);
            if (count($result) >= $params['$top']) {
                break;
            }
            $params['$skip'] += (int) $params['$top'];
        }
        echo json_encode($result);
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
        $result = $this->processLineData($data, $params);
        echo $result;
    }

    public function dashboard() {
        $params = $this->getQueryParams();
        $data = $this->callAPI($params);
        $result = $this->processDashBoardData($data, $params);
        echo $result;
    }

    public function test() {
        $params = $this->getQueryParams();
        $result = $this->Query->search($params);
        var_dump($result);
    }

    public function getCropAndMarketList() {
        $vegetables = array_keys($this->Query->vegetables);
        $fruits = array_keys($this->Query->fruits);
        $markets = array_merge(['全部'], array_keys($this->Query->markets));
        $result = array('crop' => array(
                array('name' => '全部', 'items' => array('全部'), 'cat' => 0),
                array('name' => '蔬菜', 'items' => $vegetables, 'cat' => 1),
                array('name' => '水果', 'items' => $fruits, 'cat' => 2),
            ),
            'market' => $markets);
        echo json_encode($result);
    }

}
