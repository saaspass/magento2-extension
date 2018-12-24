<?php

namespace Saaspass\Login\Block;

use Magento\Framework\View\Element\Template as BaseBlock;
use Saaspass\Login\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

class SaaspassLoginBlock extends BaseBlock
{
    /**
     * @var Data
     */
    public $helper;
    public function __construct(Context $context, Data $helper, array $data = [])
    {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
}
