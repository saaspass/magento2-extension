<?php

namespace Saaspass\Login\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Settings
{
    private $apiKey;
    private $apiPassword;
    private $is_disabled;
    private $scopeConfig;
    private $dummy;
    /**
     * Settings constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    public function setDefault()
    {
        $this->apiKey = $this->scopeConfig->getValue(
            'saaspass/general/saaspass_application_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->apiPassword = $this->scopeConfig->getValue(
            'saaspass/general/saaspass_application_pass',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->is_disabled = $this->scopeConfig->getValue(
            'saaspass/general/native_disable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return application key
     */
    public function getKey()
    {
        return $this->apiKey = $this->scopeConfig->getValue(
            'saaspass/general/saaspass_application_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return application password
     */
    public function getPass()
    {
        return $this->apiPassword = $this->scopeConfig->getValue(
            'saaspass/general/saaspass_application_pass',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return if native login is enabled or disabled
     */
    public function getIsDisabled()
    {
        return $this->is_disabled = $this->scopeConfig->getValue(
            'saaspass/general/native_disable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
