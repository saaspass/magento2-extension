<?php

namespace Saaspass\Login\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Module\Dir\Reader;
use \Magento\Framework\UrlInterface;
use Magento\Backend\Model\Auth\Session;
use \Magento\Security\Model\AdminSessionsManager;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Saaspass\Login\Helper\Settings;
use \Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Session\SessionManager;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;
    /**
     * @var UrlInterface
     */
    private $urlInterface;
    private $adminSessionsManager;
    private $settings;
    private $session;
    private $request;
    /**
     * @var Reader
     */
    private $aToken;
    private $cToken;
    private $reader;
    private $mydomain = "https://www.saaspass.com/sd/rest/applications";
    private $mytestdomain = "https://www.saaspass.com/sd/rest/magento";
    private $apiKey;
    private $apiPassword;
    private $sess;
    private $httpClientFactory;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Curl $curl,
        SessionManager $session,
        ZendClient $request,
        ZendClientFactory $httpClientFactory,
        \Saaspass\Login\Helper\Settings $settings,
        Context $context,
        Reader $reader
    ) {
        $this->settings = $settings;
        $this->_scopeConfig = $scopeConfig;
        $this->reader = $reader;
        $this->httpClientFactory = $httpClientFactory;
        parent::__construct($context);
        $this->curl = $curl;
        $this->session = $session;
        $this->request = $request;
        $this->apiKey = $this->settings->getKey();
        $this->apiPassword = $this->settings->getPass();
        $this->aToken = $this->getApplicationToken();
    }
    public function getApiKey()
    {
        return $this->settings->getKey();
    }
    public function getApiPass()
    {
        return $this->settings->getPass();
    }
    public function setApiKey()
    {
        $this->apiKey = $this->settings->getKey();
    }
    public function setApiPass()
    {
        $this->apiPassword = $this->settings->getPass();
    }
    public function getBarcode()
    {
        $url = $this->mydomain."/". $this->settings->getKey() ."/barcodes?type=IL&session="
            . $this->getSession(). "&token=". $this->getApplicationToken();
        return $this->requestBarcode($url);
    }
    public function getSession()
    {
        return $this->session->getSessionId();
    }
    public function getApplicationToken()
    {
        $url = $this->mydomain."/". $this->settings->getKey() . "/tokens?password="
            . $this->settings->getPass();
        return $this->getToken($url);
    }
    public function checkOtp($user, $otp)
    {
        $url = $this->mytestdomain ."/". $this->settings->getKey() . "/otpchecks?username="
            . $user . "&otp=" . $otp . "&token=" . $this->aToken;
        return $this->getHttpCode($url);
    }
    public function pushLogin($username, $ses)
    {
        $url = $this->mytestdomain ."/". $this->settings->getKey()
            ."/push?username=$username&session=$ses&token=$this->aToken";
        return $this->getHttpCode($url);
    }
    public function isNativeDisabled()
    {
        return $this->settings->getIsDisabled();
    }
    public function getToken($url)
    {
        try {
            $client = $this->httpClientFactory->create();
            $client->setUri($url);
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            $jsonresponse = $client->request(\Zend_Http_Client::GET)->getBody();
            if (!$jsonresponse == null) {
                $jdec = json_decode($jsonresponse);
                if (isset($jdec->{'token'})) {
                    return $jdec->{'token'};
                } else {
                    return $jsonresponse;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function checkTrackerId($tracker, $user)
    {
        $url = $this->mydomain."/". $this->apiKey."/trackers/$tracker?token=".$this->aToken."&account=$user";
        return $this->getHttpCode($url);
    }
    public function getHttpCode($url)
    {
        $client = $this->httpClientFactory->create();
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $httpcode = $client->request(\Zend_Http_Client::GET)->getStatus();
        return $httpcode;
    }
    public function requestBarcode($url)
    {
        $client = $this->httpClientFactory->create();
        $client->setUri($url);
        $data = json_decode($client->request(\Zend_Http_Client::GET)->getBody(), true);
        if (isset($data['name'])) {
            if ($data['name'] == 'EXPIRED_TOKEN') {
                $this->aToken = $this->getApplicationToken();
                $this->cToken = $this->getCompanyToken();
                requestBarcode($url);
            }
        }
        return $data['barcodeimage'];
    }
    public function checkResponse($token_url)
    {
        try {
            $client = $this->httpClientFactory->create();
            $client->setUri($token_url);
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            $httpcode = $client->request(\Zend_Http_Client::GET)->getStatus();
            if ($httpcode == '200') {
                return $httpcode;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function checkForResponse($url)
    {
        $client = $this->httpClientFactory->create();
        $client->setUri($url);
        $data = json_decode($client->request(\Zend_Http_Client::GET)->getBody(), true);
        if (isset($data['name'])) {
            if ($data['name'] == 'EXPIRED_TOKEN') {
                $this->aToken = $this->getApplicationToken();
                $this->cToken = $this->getCompanyToken();
                requestBarcode($url);
            }
        }
        return $data;
    }
}
