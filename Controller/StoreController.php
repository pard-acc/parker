<?php
namespace AntMan\Controller;

use \Samas\PHP7\Base\BaseController;
use \AntMan\Service\StoreService;

class StoreController extends BaseController
{
    /**
     * 店家詳細內容
     * @param string $args['sid']        店家 Id
     * @param string $_POST['attr_list'] 屬性清單
     */
    public function getStoreInfo()
    {
        $storeId = (int)$this->args['sid'];
        $attrList  = $this->request->getParam('attr_list', []);

        if ($storeId == 0) {
            return $this->result->invalid('不合法的 sid');
        } elseif (!is_array($attrList)) {
            return $this->result->invalid('不合法的 attr_list');
        }

        $storeService = new StoreService();
        $result = $storeService->getStoreInfo($storeId, $attrList);

        return $this->success('', $result);
    }
}
