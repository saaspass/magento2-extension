<?php

namespace Saaspass\Login\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Uninstall implements UninstallInterface
{
    private $scopeConfig;
    private $configWriter;
    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $writer
    ) {
        $this->configWriter = $writer;
    }

    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $context->getVersion();
        $setup->startSetup();
        $this->configWriter->save(
            "saaspass/general/saaspass_application_key",
            "",
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            "saaspass/general/saaspass_application_pass",
            "",
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $this->configWriter->save(
            "saaspass/general/native_disable",
            "",
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
        $setup->endSetup();
    }
}
