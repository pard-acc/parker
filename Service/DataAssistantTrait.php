<?php
namespace AntMan\Service;

use \AntMan\lib\Redis;

trait DataAssistantTrait
{
    private static $infoType = 1;
    private static $snapType = 2;
    private static $managerPath = '\\AntMan\\Manager\\';

    /**
     * 從 Redis 或 $manager 取資料
     * @param string $redisKeyPrefix  Redis Key
     * @param int    $type            儲存的資料類型 Info or Snap (資料存活時間不同)
     * @param int    $itemIdList      指定搜尋項目 ID 列表
     * @param string $managerName     manager 類別名稱
     * @param string $methodName      manager 方法名稱
     * @param array  $groupName       存入 Redis 索引 key 及回覆資料索引 key
     * @return array $info
     */
    private function getData(
        string $redisKeyPrefix,
        int $type,
        array $itemIdList,
        string $managerName,
        string $methodName,
        string $groupName
    ): array {
        $info = [];
        $waitQueryId = [];
        $redis = Redis::getRedis();

        switch ($type) {
            case self::$infoType:
                $redisKeyPrefix = $redisKeyPrefix . 'Info:';
                $expireTs = 60;
                break;
            case self::$snapType:
            default:
                $redisKeyPrefix = $redisKeyPrefix . 'Snap:';
                $expireTs = 5;
                break;
        }

        // 先從 redis 找資料
        foreach ($itemIdList as $itemId) {
            $redisKey = $redisKeyPrefix . $itemId;
            $temporaryData[$groupName] = $redis->hget($redisKey, $groupName);
            if ($temporaryData[$groupName] === false) {
                $waitQueryId[] = $itemId;
            } else {
                $info[$itemId][$groupName] = json_decode($temporaryData[$groupName], true);
            }
        }

        // redis 找不到值才請 manager 協助撈資料
        if (!empty($waitQueryId)) {
            $className = self::$managerPath . $managerName;
            $manager = new $className();
            $result = $manager->$methodName($waitQueryId);

            foreach ($waitQueryId as $id) {
                $redisKey = $redisKeyPrefix . $id;
                if (isset($result[$id])) {
                    $redis->hset($redisKey, $groupName, json_encode($result[$id], JSON_UNESCAPED_UNICODE));
                    $info[$id][$groupName] = $result[$id];
                } else {
                    $redis->hset($redisKey, $groupName, null);
                    $info[$id][$groupName] = null;
                }
                $redis->expire($redisKey, $expireTs); // 每次有新值則重置一次存活時間
            }
        }

        return $info;
    }

    /**
     * 合併主表資料及不同表的相關欄位資料並過濾特定欄位
     * @param string $dataArr         主表資料
     * @param array  $attrList        其他表的關聯欄位
     * @return array $info
     * $fieldMap = [
     *      city       (來源欄位名稱) => city            (Redis 的欄位名稱)
     *      Product_id (來源欄位名稱) => city_Product_id (Redis 的欄位名稱)
     * ]
     */
    private function filterAttr(array $dataArr, array $attrList): array
    {
        $result = [];
        $field = [];
        $append = [];
        $appendResult = [];
        foreach ($attrList as $attr) {
            // key 轉小寫, 後續取欄位統一用小寫
            $key = strtolower($attr);
            $methodName = "getDetail" . ucfirst($key);
            if (method_exists($this, $methodName)) { // 檢查所屬 class 有沒有該方法
                $append[$key] = $methodName;
            } else {
                $field[] = $attr;
            }
        }

        $idList = array_keys($dataArr);

        foreach ($append as $key => $methodName) {
            $appendResult[$key] = $this->$methodName($idList);
        }

        foreach ($dataArr as $index => $info) {
            $result[$index] = $info;
            foreach ($appendResult as $key => $injectData) {
                if (isset($injectData[$index])) {
                    $result[$index][$key] = $injectData[$index][$key];
                }
            }
        }

        return $result;
    }
}
