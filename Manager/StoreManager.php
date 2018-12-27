<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class StoreManager extends BaseManager
{
    protected $data_source = 'test';
    protected $database = 'test';
    protected $table_name = 'partner_store';

    /**
     * 獲得店家資訊
     * @param array $searchStoreId 指定搜尋店家 ID 列表
     * @return array $storeInfo 店家資訊
     */
    public function getInfo(array $searchStoreId = [])
    {
        $searchStoreCondition = empty($searchStoreId) ?
                '' :
                "AND store_id IN ('" . implode("', '", $searchStoreId) . "') ";

        $result = $this->createSQL()
                ->where('status = 1 ' . $searchStoreCondition)
                ->select();
        if (empty($result)) {
            return [];
        }

        $info = array_combine(array_column($result, 'store_id'), $result);
        return $info;
    }
}
