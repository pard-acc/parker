<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class ProductManager extends BaseManager
{
    protected $data_source = 'test';
    protected $database = 'test';
    protected $table_name = 'product';

    /**
     * 獲得商品資訊
     * @param array $searchProductId 指定搜尋商品 ID 列表
     * @return array ProductSpotInfo 商品資訊
     */
    public function getInfo(array $searchProductId = [])
    {
        $searchProductCondition = empty($searchProductId) ?
                '' :
                "AND product_id in ('" . implode("', '", $searchProductId) . "') ";

        $result = $this->createSQL()
                ->where('status = 1 ' . $searchProductCondition)
                ->select();

        if (empty($result)) {
            return [];
        }

        foreach ($result as $key => $data) {
            $result[$key]['slider_image'] = json_decode($data['slider_image'], true);
        }

        $info = array_combine(array_column($result, 'product_id'), $result);
        return $info;
    }

    /**
     * 獲得商品 ID 列表
     * @param int $status 狀態 1 = 有效, 0 = 失效
     * @return array $productIdList 商品 Id 列表
     */
    public function getProductIdList(int $status = 1): array
    {
        $productArr = $this->getInfo();
        $productIdList = [];
        foreach ($productArr as $product) {
            if ($product['status'] == $status) {
                $productIdList[] = $product['product_id'];
            }
        }

        return $productIdList;
    }
}
