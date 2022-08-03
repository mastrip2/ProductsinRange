<?php

namespace CrimsonAgility\ProductsinRange\Helper;

use Magento\Catalog\Helper\Image;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as priceHelper;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $priceHelper;
    protected $imageHelper;
    protected $_storeManager;
    protected $_appEmulation;

    public function __construct(
        Context $context,
        Image $imageHelper,
        priceHelper $priceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation
    ) {
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        $this->_appEmulation = $appEmulation;
        $this->_storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * Is this module enabled?
     * 
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null) {
        return $this->scopeConfig->getValue('', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) == 0 ? false : true;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Takes a given price and formats it correctly
     *
     * @return String
     */
    public function getPrice($price){
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Get a product image
     *
     * @param Product $product
     * @param string $image_type
     * @return string
     */
    public function getProductImageUrl($product, $image_type = 'product_thumbnail_image')
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);

        $url = $this->imageHelper->init($product, $image_type)->getUrl();

        $this->_appEmulation->stopEnvironmentEmulation();
        return $url;
    }

}
