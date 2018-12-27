<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class ProductMapLabelManager extends BaseManager
{
    protected $data_source    = 'test';
    protected $database       = 'test';
    protected $table_name     = 'product_map_label';

    /**
     * 獲得商品標籤資訊
     * @param array $searchProductId  指定搜尋商品 ID 列表
     * @param int   $searchLabel      指定搜尋的標籤 ID (0 = 全部)
     * @return array $info 商品標籤資訊
     */
    public function getInfo(array $searchProductId = [], int $searchLabel = 0)
    {
        $searchProductCondition = empty($searchProductId) ?
                '' :
                "AND product_id in ('" . implode("', '", $searchProductId) . "') ";

        $result = $this->createSQL()
                ->field(['product_id', 'label_id'])
                ->where('status = 1 ' . $searchProductCondition)
                ->select();
        if (empty($result)) {
            return [];
        }

        $info = [];
        foreach ($result as $data) {
            $info[$data['product_id']]['label_arr'][] = $data['label_id'];
        }

        if ($searchLabel != 0) {
            foreach ($info as $key => $data) {
                if (!in_array($searchLabel, $data['label_arr'])) {
                    unset($info[$key]);
                }
            }
        }

        return $info;
    }
}
