<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class ProductMapCityManager extends BaseManager
{
    protected $data_source    = 'test';
    protected $database       = 'test';
    protected $table_name     = 'product_map_city';

    /**
     * 獲得商品城市資訊
     * @param array $searchProductId 指定搜尋商品 ID 列表
     * @param int   $searchCity      指定搜尋的城市 ID (0 = 全部)
     * @return array $info 商品城市資訊
     */
    public function getInfo(array $searchProductId = [], int $searchCity = 0)
    {
        $searchProductCondition = empty($searchProductId) ?
                '' :
                "AND product_id in ('" . implode("', '", $searchProductId) . "') ";

        $result = $this->createSQL()
                ->field(['product_id', 'city_id'])
                ->where('status = 1 ' . $searchProductCondition)
                ->select();
        if (empty($result)) {
            return [];
        }

        $info = [];
        foreach ($result as $data) {
            $info[$data['product_id']]['city_arr'][] = $data['city_id'];
        }

        if ($searchCity != 0) {
            foreach ($info as $key => $data) {
                if (!in_array($searchCity, $data['city_arr'])) {
                    unset($info[$key]);
                }
            }
        }

        return $info;
    }
}
