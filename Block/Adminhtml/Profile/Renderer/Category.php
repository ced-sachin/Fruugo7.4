<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_Walmart
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\Fruugo\Block\Adminhtml\Profile\Renderer;
class Category extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $categoryFactory;
    protected $productFactory;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $product = $this->productFactory->create()->load($row->getEntityId());
        $cats = $product->getCategoryIds();
        $allCats = '';
        foreach($cats as $key => $cat)
        {
            $_category = $this->categoryFactory->create()->load($cat);
            $allCats.= $_category->getName();
            if($key < count($cats)-1)
                $allCats.= ',<br />';
        }
        return $allCats;
    }
}