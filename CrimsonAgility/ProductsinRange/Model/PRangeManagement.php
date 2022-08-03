<?php
declare(strict_types=1);

namespace CrimsonAgility\ProductsinRange\Model;

use CrimsonAgility\ProductsinRange\Helper\Data as helper;

class PRangeManagement implements \CrimsonAgility\ProductsinRange\Api\PRangeManagementInterface
{

    protected $_productCollectionFactory;
    protected $productRepository;
    /**
     * @var \CrimsonAgility\ProductsinRange\Helper\Data helper
     */
    protected $helper;

    private $error;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Helper $helper
    )
    {
        $this->_productCollectionFactory    = $productCollectionFactory;
        $this->productRepository            = $productRepository;
        $this->helper                       = $helper;
        $this->error                        = false;
    }

    /**
     * {@inheritdoc}
     */
    public function postPRange($low, $high, $sort = '', $limit = 10)
    {
        // Validation
        $this->isValid($low, $high);
        if(!empty($this->error)){
            return json_encode($this->error);
        }

        $products = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('price', array('from'=>$low,'to'=>$high));
        $collection->setPageSize($limit); // fetching only x products

        //Set collection sort
        if(empty($sort) || $sort == 'ASC'){
            $collection->setOrder('price','ASC');
        }
        else{
            $collection->setOrder('price','DESC');
        }

        foreach ($collection as $prod) {
            $x = $this->getProductBySku($prod->getSku());
            $a = $this->helper->only($x->getData(), ['sku', 'status', 'name', 'quantity_and_stock_status', 'price']);

            $a['price'] = $this->helper->getPrice($a['price']);
            $a['thumbnail'] = $this->helper->getProductImageUrl($x);
            $a['qty'] = $a['quantity_and_stock_status']['qty'];
            $a['in_stock'] = $a['quantity_and_stock_status']['is_in_stock'];
            $a['productUrl'] = $x->getProductUrl();

            unset($a['quantity_and_stock_status']);
            $products[] = $a;
        }

        return $products;
    }

    /**
     * Get product by sku
     * 
     * @param mixed $sku
     * 
     * @return productRepository
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * input validation
     * 
     * @param int $low
     * @param int $high
     */
    public function isValid($low, $high){
        $max = 5;
        $maxTimes = 5;

        //Low cannot be negative
        if($low < 0){
            $this->setError('Low price cannot be negative');
        }

        //High must never be lower than low
        if($low > $high){
            $this->setError('High price cannot be greater than low price');
        }
        
        //High must never be beyond x times low
        if($low > 0){
            $max = $low * $maxTimes;
        }

        if($high > $max){
            $this->setError('High price cannot be greater than '. $maxTimes .'x of low');
        }
    }

    /**
     * Set Error Message and flag
     * 
     * @param $message
     */
    public function setError($message){
        $this->error = [
                    'error' => true,
                    'message' => $message
                ];;
    }
}

