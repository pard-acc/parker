<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class ProductMapSpotManager extends BaseManager
{
    protected $data_source    = 'test';
    protected $database       = 'test';
    protected $table_name     = 'product_map_spot';

    /**
     * 獲得商品景點資訊
     * @param array $searchProductId 指定搜尋商品 ID 列表
     * @param int   $searchSpot      指定搜尋的景點 ID (0 = 全部)
     * @return array $info 商品景點資訊
     */
    public function getInfo(array $searchProductId = [], int $searchSpot = 0)
    {
        $searchProductCondition = empty($searchProductId) ?
                '' :
                "AND product_id in ('" . implode("', '", $searchProductId) . "') ";

        $result = $this->createSQL()
                ->field(['product_id', 'spot_id'])
                ->where('status = 1 ' . $searchProductCondition)
                ->select();
        if (empty($result)) {
            return [];
        }

        $info = [];
        foreach ($result as $data) {
            $info[$data['product_id']]['spot_arr'][] = $data['spot_id'];
        }

        if ($searchSpot != 0) {
            foreach ($info as $key => $data) {
                if (!in_array($searchSpot, $data['spot_arr'])) {
                    unset($info[$key]);
                }
            }
        }

        return $info;
    }
}
