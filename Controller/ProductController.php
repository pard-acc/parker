<?php
namespace AntMan\Controller;

use \Samas\PHP7\Base\BaseController;
use \Samas\PHP7\Kit\StrKit;
use \AntMan\Service\ProductService;

class ProductController extends BaseController
{
    /**
     * 單檔商品詳細內容
     * @param string $args['pid']        商品 Id
     * @param string $_POST['attr_list'] 屬性清單
     */
    public function getProductInfo()
    {
        $productId = (int)$this->args['pid'];
        $attrList  = $this->request->getParam('attr_list', []);

        if ($productId == 0) {
            return $this->result->invalid('不合法的 pid');
        } elseif (!is_array($attrList)) {
            return $this->result->invalid('不合法的 attr_list');
        }

        $productService = new ProductService();
        $result = $productService->getProductInfo($productId, $attrList);

        return $this->success('', $result);
    }

    /**
     * 商品列表
     * @param int   $_POST['channel']   頻道
     * @param int   $_POST['city']      城市
     * @param int   $_POST['category']  類別
     * @param int   $_POST['label']     標籤
     * @param int   $_POST['spot']      景點
     * @param int   $_POST['start_ts']  上架時間
     * @param int   $_POST['end_ts']    下架時間
     * @param array $_POST['item_list'] 屬性清單
     */
    public function getProductList()
    {
        $channel  = $this->request->getParam('channel');
        $city     = $this->request->getParam('city');
        $category = $this->request->getParam('category');
        $label    = $this->request->getParam('label');
        $spot     = $this->request->getParam('spot');
        $startTs  = $this->request->getParam('start_ts');
        $endTs    = $this->request->getParam('end_ts');
        $attrList = $this->request->getParam('attr_list', []);

        if (!is_array($attrList)) {
            return $this->result->invalid('不合法的 attr_list');
        } elseif (!is_null($channel) && !StrKit::checkInt($channel)) {
            return $this->result->invalid('不合法的 channel');
        } elseif (!is_null($city) && !StrKit::checkInt($city)) {
            return $this->result->invalid('不合法的 city');
        } elseif (!is_null($category) && !StrKit::checkInt($category)) {
            return $this->result->invalid('不合法的 category');
        } elseif (!is_null($label) && !StrKit::checkInt($label)) {
            return $this->result->invalid('不合法的 label');
        } elseif (!is_null($spot) && !StrKit::checkInt($spot)) {
            return $this->result->invalid('不合法的 spot');
        } elseif (!is_null($startTs) || !is_null($endTs)) {
            if (!StrKit::checkInt($startTs)) {
                return $this->result->invalid('不合法的 start_ts');
            } elseif (!StrKit::checkInt($endTs)) {
                return $this->result->invalid('不合法的 end_ts');
            }
        }

        $productService = new ProductService();
        $result = $productService->getProductList(
            (int)$channel,
            (int)$city,
            (int)$category,
            (int)$label,
            (int)$spot,
            (int)$startTs,
            (int)$endTs,
            $attrList
        );
        return $this->success('', $result);
    }
}
