<?php
namespace AntMan\Service;

use \AntMan\Manager\ProductManager;
use \AntMan\Manager\ProductMapChannelManager;
use \AntMan\Manager\ProductSkuManager;
use \AntMan\Manager\ProductMapCityManager;
use \AntMan\Manager\ProductMapCategoryManager;
use \AntMan\Manager\ProductMapLabelManager;
use \AntMan\Manager\ProductMapSpotManager;

class ProductService
{
    use DataAssistantTrait;

    protected $redisKeyPrefix = 'ProductService:';

    /**
     * 搜尋商品資訊
     * @param int/array $productId 指定搜尋 ProductId
     * @param array     $attrList 需獲取的欄位列表
     * @return array $productInfo 商品資訊
     */
    public function getProductInfo($productId, array $attrList = []): array
    {
        $searchProductId = is_int($productId) ? [$productId] : $productId;

        $product = $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchProductId,
            'ProductManager',
            'getInfo',
            'product'
        );

        if (empty($product)) {
            return [];
        }

        $productInfo = $this->filterAttr($product, $attrList);
        return $productInfo;
    }

    /**
     * 搜尋商品列表
     * @param int   $channel       頻道
     * @param int   $city          城市
     * @param int   $category      類別
     * @param int   $label         標籤
     * @param int   $spot          景點
     * @param int   $startTs       上架時間
     * @param int   $endTs         下架時間
     * @param array $attrList      項目列表
     * @param array $productIdList 指定搜尋 ProductId 列表
     * @return array $product 商品列表
     */
    public function getProductList(
        int $channel = 0,
        int $city = 0,
        int $category = 0,
        int $label = 0,
        int $spot = 0,
        int $startTs = 0,
        int $endTs = 0,
        array $attrList = [],
        array $productIdList = []
    ): array {
        if (empty($productIdList)) {      // 未傳入 pid 列表 表示全部的 pid
            $productManager = new ProductManager();
            $searchProductId = $productManager->getProductIdList();
        }

        if ($startTs > 0 && $endTs > 0) { // 時間區間過濾
            $searchProductId = $this->getTimeIntervalProductID($startTs, $endTs, $searchProductId);
            if (empty($searchProductId)) {
                return [];
            }
        }

        if (!empty($channel)) {           // 依頻道過濾
            $searchProductId = $this->getChannelProductID($channel, $searchProductId);
            if (empty($searchProductId)) {
                return [];
            }
        }

        if (!empty($city)) {              // 依城市過濾
            $searchProductId = $this->getCityProductID($city, $searchProductId);
            if (empty($searchProductId)) {
                return [];
            }
        }

        if (!empty($category)) {          // 依類別過濾
            $searchProductId = $this->getCategoryProductID($category, $searchProductId);
            if (empty($searchProductId)) {
                return [];
            }
        }

        if (!empty($label)) {             // 依標籤過濾
            $searchProductId = $this->getLabelProductID($label, $searchProductId);
            if (empty($searchProductId)) {
                return [];
            }
        }

        if (!empty($spot)) {             // 依景點過濾
            $searchProductId = $this->getSpotProductID($spot, $searchProductId);
            if (empty($searchProductId)) {
                return [];
            }
        }

        $productList = $this->getProductInfo($searchProductId, $attrList);

        return $productList;
    }

    /**
     * 搜尋頻道商品 ID 清單
     * @param int   $channel         頻道
     * @param array $searchProductId 指定搜尋 ProductId 列表
     * @return array $inChannelProductId 頻道商品 ID
     */
    public function getChannelProductID(int $channel, array $searchProductId = []): array
    {
        $productMapChannelManager = new ProductMapChannelManager();
        $inChannelProduct = $productMapChannelManager->getInfo($searchProductId, $channel);
        return array_keys($inChannelProduct);
    }

    /**
     * 搜尋城市商品 ID 清單
     * @param int   $city            城市
     * @param array $searchProductId 指定搜尋 ProductId 列表
     * @return array $inCityProductId 城市商品 ID
     */
    public function getCityProductID(int $city, array $searchProductId = []): array
    {
        $productMapCityManager = new ProductMapCityManager();
        $inCityProduct = $productMapCityManager->getInfo($searchProductId, $city);
        return array_keys($inCityProduct);
    }

    /**
     * 搜尋類別商品 ID 清單
     * @param int   $category         類別
     * @param array $searchProductId  指定搜尋 ProductId 列表
     * @return array $inCategoryProductId 城市商品 ID
     */
    public function getCategoryProductID(int $category, array $searchProductId = []): array
    {
        $productMapCategoryManager = new ProductMapCategoryManager();
        $inCategoryProduct = $productMapCategoryManager->getInfo($searchProductId, $category);
        return array_keys($inCategoryProduct);
    }

    /**
     * 搜尋標籤商品 ID 清單
     * @param int   $label           標籤 ID
     * @param array $searchProductId 指定搜尋 ProductId 列表
     * @return array $inCityProductId 城市商品 ID
     */
    public function getLabelProductID(int $label, array $searchProductId = []): array
    {
        $productMapLabelManager = new ProductMapLabelManager();
        $inLabelProduct = $productMapLabelManager->getInfo($searchProductId, $label);
        return array_keys($inLabelProduct);
    }

    /**
     * 搜尋景點商品 ID 清單
     * @param int   $spot            景點 ID
     * @param array $searchProductId 指定搜尋 ProductId 列表
     * @return array $inSpotProductId 景點商品 ID
     */
    public function getSpotProductID(int $spot, array $searchProductId = []): array
    {
        $productMapSpotManager = new ProductMapSpotManager();
        $inSpotProduct = $productMapSpotManager->getInfo($searchProductId, $spot);
        return array_keys($inSpotProduct);
    }

    /**
     * 搜尋指定時間內商品 ID 清單
     * @param int $startTs         上架時間
     * @param int $endTs           下架時間
     * @param int $searchProductId 指定搜尋 ProductId 列表
     * @return array $inCityProductId 指定時間商品 ID
     */
    public function getTimeIntervalProductID(int $startTs, int $endTs, array $searchProductId = []): array
    {
        $productSkuManager = new ProductSkuManager();
        $inTimeProduct = $productSkuManager->getTimeIntervalProduct($startTs, $endTs, $searchProductId);
        return array_keys($inTimeProduct);
    }

    /**
     * 商品細節 City
     * @param int    $searchProductId 指定搜尋 ProductId 列表
     * @return array $productInfo     商品資訊
     */
    public function getDetailCity(array $searchProductId): array
    {
        return $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchProductId,
            'ProductMapCityManager',
            'getInfo',
            'city'
        );
    }

    /**
     * 商品細節 Channl
     * @param int    $searchProductId 指定搜尋 ProductId 列表
     * @return array $productInfo     商品資訊
     */
    public function getDetailChannel(array $searchProductId): array
    {
        return $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchProductId,
            'ProductMapChannelManager',
            'getInfo',
            'channel'
        );
    }

    /**
     * 商品細節 Category
     * @param int    $searchProductId 指定搜尋 ProductId 列表
     * @return array $productInfo     商品資訊
     */
    public function getDetailCategory(array $searchProductId): array
    {
        return $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchProductId,
            'ProductMapCategoryManager',
            'getInfo',
            'category'
        );
    }

    /**
     * 商品細節 Label
     * @param int    $searchProductId 指定搜尋 ProductId 列表
     * @return array $productInfo     商品資訊
     */
    public function getDetailLabel(array $searchProductId): array
    {
        return $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchProductId,
            'ProductMapLabelManager',
            'getInfo',
            'label'
        );
    }

    /**
     * 商品細節 Spot
     * @param int $searchProductId 指定搜尋 ProductId 列表
     * @return array $productInfo 商品資訊
     */
    public function getDetailSpot(array $searchProductId): array
    {
        return $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchProductId,
            'ProductMapSpotManager',
            'getInfo',
            'spot'
        );
    }

    /**
     * 商品細節 Item
     * @param int $searchProductId 指定搜尋 ProductId 列表
     * @return array $productInfo 商品資訊
     */
    public function getDetailItem(array $searchProductId): array
    {
        return $this->getData(
            $this->redisKeyPrefix,
            self::$infoType,
            $searchProductId,
            'ProductItemManager',
            'getProductItem',
            'item'
        );
    }
}
