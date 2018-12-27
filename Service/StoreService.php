<?php
namespace AntMan\Service;

class StoreService
{
    use DataAssistantTrait;

    protected $redisKeyPrefix = 'StoreService:';

    /**
     * 搜尋商品資訊
     * @param int/array $storeId 指定搜尋店家 id
     * @param array     $attrList 需獲取的欄位列表
     * @return array $storeInfo 店家資訊
     */
    public function getStoreInfo($storeId, array $attrList = []): array
    {
        $searchStoreId = is_int($storeId) ? [$storeId] : $storeId;
        $store = $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchStoreId,
            'StoreManager',
            'getInfo',
            'store'
        );

        if (empty($store)) {
            return [];
        }

        $storeInfo = $this->filterAttr($store, $attrList);
        return $storeInfo;
    }
}
