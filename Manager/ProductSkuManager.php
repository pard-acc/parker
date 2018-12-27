<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;
use \AntMan\lib\GRedis;

class ProductSkuManager extends BaseManager
{
    protected $data_source    = 'test';
    protected $database       = 'test';
    protected $table_name     = 'product_sku';
    protected $keyPrefix      = 'TimeIntervalProduct:';

    /**
     * 獲得指定上下架時間的商品資訊
     * @param int $startTs         商品上架時間
     * @param int $endTs           商品下架時間
     * @param int $searchProductId 指定搜尋 ProductId 列表
     * @return array $productInfo 商品資訊
     */
    public function getTimeIntervalProduct(int $startTs, int $endTs, array $searchProductId = [])
    {
        $redis = GRedis::getRedis();
        $key = $this->keyPrefix . $startTs . '_' . $endTs;
        $expireTs = 300;

        if (!$redis->exists($key)) {
            $result = $this->createSQL()
                            ->field(['product_id', 'publish_start_time', 'publish_end_time'])
                            ->where(
                                'publish_start_time >= :startTime AND ' .
                                    'publish_end_time <= :endTime',
                                [
                                    ':startTime' => date('Y-m-d H:i:s', $startTs),
                                    ':endTime'   => date('Y-m-d H:i:s', $endTs)
                                ]
                            )
                            ->select();
            $productInfo = array_combine(array_column($result, 'product_id'), $result);
            $redis->set($key, json_encode($productInfo, JSON_UNESCAPED_UNICODE));
            $redis->expire($key, $expireTs);
        } else {
            $productInfo = json_decode($redis->get($key), true);
        }

        if (!empty($searchProductId)) {
            $pickInfo = [];
            foreach ($searchProductId as $pid) {
                if (isset($productInfo[$pid])) {
                    $pickInfo[$pid] = $productInfo[$pid];
                }
            }

            return $pickInfo;
        }

        return $productInfo;
    }

    /**
     * 清除暫存資料
     * @return array $productInfo 商品資訊
     */
    public function clearTemporaryData()
    {
        $redis = GRedis::getRedis();
        $keyPrefix = $this->keyPrefix . '*';
        $keyArr = $redis->keys($keyPrefix);

        foreach ($keyArr as $key) {
            $redis->del($key);
        }
    }
}
