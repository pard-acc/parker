<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class ProductMapCategoryManager extends BaseManager
{
    protected $data_source    = 'test';
    protected $database       = 'test';
    protected $table_name     = 'product_map_category';

    /**
     * 獲得商品類別資訊
     * @param array $searchProductId   指定搜尋商品 ID 列表
     * @param int   $searchCategory    指定搜尋的類別 ID (0 = 全部)
     * @return array $info 商品類別資訊
     */
    public function getInfo(array $searchProductId = [], int $searchCategory = 0)
    {
        $searchProductCondition = empty($searchProductId) ?
                '' :
                "AND product_id in ('" . implode("', '", $searchProductId) . "') ";

        $result = $this->createSQL()
                ->field(['product_id', 'category_id'])
                ->where('status = 1 ' . $searchProductCondition)
                ->select();
        if (empty($result)) {
            return [];
        }

        $info = [];
        foreach ($result as $data) {
            $info[$data['product_id']]['category_arr'][] = $data['category_id'];
        }

        if ($searchCategory != 0) {
            foreach ($info as $key => $data) {
                if (!in_array($searchCategory, $data['category_arr'])) {
                    unset($info[$key]);
                }
            }
        }

        return $info;
    }
}
