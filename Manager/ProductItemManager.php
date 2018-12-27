<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class ProductItemManager extends BaseManager
{
    protected $data_source = 'test';
    protected $database = 'test';
    protected $table_name = 'product_item';

    /**
     * 獲得商品方案資訊
     * @param array $searchProductId 指定搜尋商品 ID 列表
     * @return array $productItem    商品方案資訊
     */
    public function getProductItem(array $searchProductId = [])
    {
        $searchProductCondition = empty($searchProductId) ?
                '' :
                "AND product_id in ('" . implode("', '", $searchProductId) . "') ";

        $result = $this->createSQL()
                        ->field('product_item_id, product_id, name, original_price, ticket_price, price, average_price')
                        ->where('status = 1 ' . $searchProductCondition)
                        ->select();
        if (empty($result)) {
            return [];
        }

        $productItem = [];
        foreach ($result as $data) {
            $pid = $data['product_id'];
            unset($data['product_id']);
            $productItem[$pid][] = $data;
        }

        return $productItem;
    }
}
