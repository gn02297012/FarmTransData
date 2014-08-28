<?php

App::uses('AppModel', 'Model');

class Query extends AppModel {

    public $name = 'Query';
    public $useTable = 'raw';
    public $primaryKey = 'id';
    public $belongsTo = array(
        'Code' => array(
            'className' => 'Code',
            'foreignKey' => 'code',
            'fields' => array('category'),
        )
    );
    public $markets = array(
        '台北二' => 104, '台北一' => 109, '三重市' => 241,
        '宜蘭市' => 260, '桃園縣' => 338, '台中市' => 400,
        '東勢鎮' => 423, '永靖鄉' => 512, '溪湖鎮' => 514,
        '南投市' => 540, '嘉義市' => 600,
        '西螺鎮' => 648, '高雄市' => 800, '鳳山區' => 830,
        '屏東市' => 900, '台東市' => 930, '花蓮市' => 950,
        '台北市場' => 105, '台南市場' => 700,
    );
    public $vegetables = array(
        '九層塔' => 1, '人參葉' => 1, '大心菜' => 1, '大蒜' => 1, '小白菜' => 1,
        '巴西利' => 1, '巴西蘑菇' => 1, '毛豆' => 1,
        '水蓮' => 1, '牛蒡' => 1, '冬瓜' => 1, '包心白' => 1, '包心白菜' => 1, '半天花' => 1,
        '半天筍' => 1, '玉米' => 1, '甘蔗筍' => 1, '甘藷' => 1, '甘薯' => 1, '甘藷葉' => 1,
        '甘藍' => 1, '石蓮花' => 1, '朴菜' => 1, '百合' => 1, '百果' => 1,
        '竹筍' => 1, '西洋菜' => 1, '杏鮑菇' => 1, '秀珍菇' => 1, '秀珍菇(盒)' => 1, '芋' => 1,
        '豆薯' => 1, '其他' => 1, '其他花類' => 1, '其他菇類' => 1, '其他蔬菜' => 1, '松茸' => 1,
        '油菜' => 1, '芥菜' => 1, '芥藍菜' => 1, '芫荽' => 1, '花豆' => 1,
        '花胡瓜' => 1, '花椰菜' => 1, '芹菜' => 1, '芽菜類' => 1, '虎豆' => 1,
        '金針花' => 1, '金針筍' => 1, '金絲菇' => 1, '金絲菇(盒)' => 1, '青江白菜' => 1,
        '青花苔' => 1, '青蔥' => 1, '南瓜' => 1, '扁蒲' => 1,
        '柳松菇' => 1, '洋菇' => 1, '洋菇(盒)' => 1, '洋蔥' => 1, '洛神花' => 1, '珊瑚菇' => 1,
        '珍珠菜' => 1, '皇宮菜' => 1, '紅鳳菜' => 1, '胡瓜' => 1, '胡蘿蔔' => 1,
        '苦瓜' => 1, '茄子' => 1, '韭菜' => 1, '香椿' => 1, '海菜' => 1,
        '茭白筍' => 1, '茴香' => 1, '茼蒿' => 1, '草石蠶' => 1, '草菇' => 1,
        '隼人瓜' => 1, '馬鈴薯' => 1, '敏豆' => 1, '晚香玉筍' => 1, '桶筍' => 1,
        '球莖甘藍' => 1, '甜椒' => 1, '荸薺' => 1, '莧菜' => 1, '雪裡紅' => 1,
        '猴頭菇' => 1, '蕃茄' => 1, '筍片' => 1, '筍茸' => 1, '筍乾' => 1,
        '筍絲' => 1, '絲瓜' => 1, '菊芋' => 1, '菜豆' => 1, '菠菜' => 1,
        '菱角' => 1, '菾菜' => 1, '萊豆' => 1, '越瓜' => 1, '黃秋葵' => 1,
        '黑甜仔菜' => 1, '塌棵菜' => 1, '慈菇' => 1, '萵苣莖' => 1, '萵苣菜' => 1,
        '落花生' => 1, '辣椒' => 1, '樊花' => 1, '熟筍' => 1,
        '蓮藕' => 1, '豌豆' => 1, '豬母菜' => 1, '醃瓜' => 1, '蕎頭' => 1,
        '蕨菜' => 1, '濕木耳' => 1, '濕木耳(盒)' => 1, '溼香菇' => 1, '蕹菜' => 1, '薑' => 1,
        '薯蕷' => 1, '鴻禧菇' => 1, '薺菜' => 1, '瓊花' => 1, '藤川七' => 1,
        '鵲豆' => 1, '蘆筍' => 1, '蠔菇' => 1, '蠔菇(盒)' => 1, '鹹菜' => 1, '蘿蔔' => 1,
        '蘿蔔乾' => 1, '蠶豆' => 1,
    );
    public $fruits = array(
        '小番茄' => 1, '山竹' => 1, '木瓜' => 1, '水蜜桃' => 1, '火龍果' => 1,
        '甘蔗' => 1, '石榴' => 1, '百香果' => 1, '西瓜' => 1, '佛利檬' => 1,
        '李' => 1, '芒果' => 1, '其他' => 1, '奇異果' => 1, '枇杷' => 1,
        '波羅蜜' => 1, '虎頭柑' => 1, '柚子' => 1, '文旦柚' => 1, '西施柚' => 1,
        '西洋梨' => 1, '柿子' => 1, '柿餅' => 1, '洋香瓜' => 1,
        '紅毛丹' => 1, '紅柑' => 1, '茂谷柑' => 1, '香瓜梨' => 1, '香蕉' => 1,
        '香櫞' => 1, '栗子' => 1, '桃子' => 1, '桑椹' => 1, '海梨' => 1,
        '草莓' => 1, '荔枝' => 1, '桶柑' => 1, '梅' => 1, '梨' => 1,
        '甜瓜' => 1, '甜桃' => 1, '甜橙' => 1, '蛋黃果' => 1, '棗子' => 1,
        '椪柑' => 1, '椪柑(其他)' => 1, '番石榴' => 1, '黃金果' => 1, '椰子' => 1, '楊桃' => 1,
        '楊梅' => 1, '溫州柑' => 1, '葡萄' => 1, '葡萄柚' => 1,
        '酪梨' => 1, '榴槤' => 1, '蜜棗' => 1, '鳳梨' => 1, '蓮霧' => 1,
        '橄欖' => 1, '龍眼' => 1, '藍莓' => 1, '雜柑' => 1, '蘋果' => 1,
        '釋迦' => 1, '櫻桃' => 1, '豔陽柑' => 1, '檸檬' => 1,
    );

    public function &search($params) {
        //params的參數與conditions的參數對照
        $keys = array(
            'Crop' => 'Query.name like', 'Market' => 'Query.market'
        );
        //產生查詢條件
        $conditions = array();
        foreach ($keys as $key => &$value) {
            if (!empty($params[$key])) {
                $conditions[$value] = $params[$key];
            }
        }
        $startDate = empty($params['StartDate']) ? 1 : str_replace('.', '', $params['StartDate']);
        $endDate = empty($params['EndDate']) ? 1 : str_replace('.', '', $params['EndDate']);
        $conditions['Query.date_int BETWEEN ? AND ?'] = array($startDate, $endDate);
        $conditions['Code.category BETWEEN ? AND ?'] = array(1, 2);
        //如果有設定Crop參數，則在查詢後加個%
        if (isset($conditions[$keys['Crop']])) {
            $conditions[$keys['Crop']] .= '%';
        }
        $result = $this->find('all', array(
            'conditions' => $conditions,
            'recursive' => 1,
            'order' => array('date desc'),
            'limit' => $params['$top'],
            'offset' => $params['$skip'],
        ));

        //var_dump($this->getLastQuery());
        //整理結果，去除掉陣列中的Query跟Code，變成SQL出來的結果
        function getCategory($cat) {
            switch ($cat) {
                case '1':
                    return '蔬菜';
                case '2':
                    return '水果';
                case '3':
                    return '花卉';
                default:
                    return '其他';
            }
        }

        foreach ($result as &$item) {
            $item = array_merge($item['Query'], $item['Code']);
            $item['code'] = getCategory($item['code']);
        }
        return $result;

        //更新date_int
        // update `raw` set `date_int` = replace(`date`,'.','') where date_int='0';
    }

    public function getLastQuery() {
        $dbo = $this->getDatasource();
        $logs = $dbo->getLog();
        $lastLog = end($logs['log']);
        return $lastLog['query'];
    }

}
