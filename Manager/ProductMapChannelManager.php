<?php
namespace AntMan\Manager;

use \Samas\PHP7\Base\BaseManager;

class ProductMapChannelManager extends BaseManager
{
    protected $data_source    = 'test';
    protected $database       = 'test';
    protected $table_name     = 'product_map_channel';

    /**
     * 獲得商品頻道資訊
     * @param array $searchProductId 指定搜尋商品 ID 列表
     * @param int   $searchChannel   指定搜尋的頻道 ID (0 = 全部)
     * @return array $info 商品頻道資訊
     */
    public function getInfo(array $searchProductId = [], int $searchChannel = 0)
    {
        $searchProductCondition = empty($searchProductId) ?
                '' :
                "AND product_id in ('" . implode("', '", $searchProductId) . "') ";

        $result = $this->createSQL()
                ->field(['product_id', 'channel_id', 'is_main'])
                ->where('status = 1 ' . $searchProductCondition)
                ->select();
        if (empty($result)) {
            return [];
        }

        $info = [];
        foreach ($result as $data) {
            $info[$data['product_id']]['channel_arr'][] = $data['channel_id'];
            if (!empty($data['is_main'])) {
                $info[$data['product_id']]['main_channel'] = $data['channel_id'];
            }
        }

        if ($searchChannel != 0) {
            foreach ($info as $key => $data) {
                if (!in_array($searchChannel, $data['channel_arr'])) {
                    unset($info[$key]);
                }
            }
        }

        return $info;
    }
}
