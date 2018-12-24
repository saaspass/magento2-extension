<?php

namespace Saaspass\Login\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Backend\Model\Auth\Session;
use Saaspass\Login\Helper\Data;
use Magento\User\Model\UserFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use \Magento\Framework\Event\ManagerInterface as EventManager;
use \Magento\Framework\Registry;
use \Magento\Security\Model\AdminSessionsManager;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Session\SessionManager;

/**
 * Class SaaspassLoginObserver
 * @package Saaspass\Login\Observer
 * @ Replace using of deprecated method \Magento\Framework\Model\AbstractModel:load()
 */
class SaaspassLoginObserver implements ObserverInterface
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Session
     */
    private $session;
    private $sessionId;
    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var AdminSessionsManager
     */
    private $adminSessionsManager;
    private $urlInterface;
    private $resultRedirect;
    private $context;
    private $driver;
    /**
     * @var DateTime
     */
    private $dateTime;
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Data $helper,
        SessionManager $sessionId,
        Session $session,
        File $driver,
        RequestInterface $request,
        UserFactory $userFactory,
        MessageManager $messageManager,
        Registry $registry,
        EventManager $eventManager,
        AdminSessionsManager $adminSessionsManager,
        DateTime $dateTime
    ) {
        $this->resultRedirect = $result;
        $this->helper = $helper;
        $this->sessionId = $sessionId;
        $this->session = $session;
        $this->driver = $driver;
        $this->request = $request;
        $this->userFactory = $userFactory;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->adminSessionsManager = $adminSessionsManager;
        $this->dateTime = $dateTime;
        $this->urlInterface = $context->getUrlBuilder();
        $this->_scopeConfig = $scopeConfig;
        $this->context = $context;
    }
    /**
     * @param Observer $observer
     * @throws AuthenticationException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(Observer $observer)
    {
        $observer->getEventName();
        $session = $this->sessionId->getSessionId();
        //Start of barcode code and pushLogin
        if ($this->driver->isExists($session . '.txt')) {
            $user = $this->driver->fileGetContents($session . '.txt');
            $this->simulateSignon($user);
        }
        //End of barcode code (working) and pushLogin (working)
        if ($this->request->isPost() && $this->helper->isNativeDisabled() == 1) {
            $user = $this->context->getRequest()->getParam('user') != null ?
                $this->context->getRequest()->getParam('user') : '';
            $pass = $this->context->getRequest()->getParam("password") != null ?
                $this->context->getRequest()->getParam("password") : '';
            $otp = $this->context->getRequest()->getParam("otp") != null ?
                $this->context->getRequest()->getParam("otp") : '';
            $this->nativeLoginEnabled($user, $pass, $otp, $session);
        } elseif ($this->request->isPost() && $this->helper->isNativeDisabled() == 0) {
            $user = $this->context->getRequest()->getParam('user') != null ?
                $this->context->getRequest()->getParam('user') : '';
            $pass = $this->context->getRequest()->getParam("password") != null ?
                $this->context->getRequest()->getParam("password") : '';
            $otp = $this->context->getRequest()->getParam("otp") != null ?
                $this->context->getRequest()->getParam("otp") : '';
            $this->nativeLoginDisabled($user, $pass, $otp, $session);
        }
        if (array_key_exists("tracker", $this->context->getRequest()->getParams()) &&
            array_key_exists("account", $this->context->getRequest()->getParams())) {
            $trackerID = $this->context->getRequest()->getParam("tracker");
            $username = $this->context->getRequest()->getParam("account");
            if ($this->helper->checkTrackerId($trackerID, $username) == 200) {
                $this->simulateSignon($username);
                $this->sessionId->writeClose();
            }
        }
    }
    private function nativeLoginEnabled($user, $pass, $otp, $session)
    {
        if ($user != "" && $pass != "" && $otp != "") {
            if ($this->helper->checkOtp($user, $otp) == 200) {
                try {
                    $this->login($user, $pass);
                } catch (AuthenticationException $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            } else {
                $this->messageManager->addError("Wrong username or otp");
            }
        } elseif ($user != "" && $pass == "" && $otp == "") {
            $this->helper->pushLogin($user, $session);
        }
    }
    private function nativeLoginDisabled($user, $pass, $otp, $session)
    {
        if ($pass != "") {
            $this->login($user, $pass);
        } elseif ($pass == "" && $otp != "") {
            if ($this->helper->checkOtp($user, $otp) == 200) {
                $this->simulateSignon($user);
            }
        } elseif ($user != "" && $pass == "" && $otp == "") {
            $this->helper->pushLogin($user, $session);
        }
    }
    /**
     * @param $username
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function simulateSignon($username)
    {
        try {
            $user = $this->userFactory->create()->load($username, 'username');
            if ($user->getId()) {
                if ($user->getIsActive() != '1') {
                    throw new AuthenticationException(
                        __('You did not sign in correctly or your account is temporarily disabled.')
                    );
                }
                if (!$user->hasAssigned2Role($user->getId())) {
                    throw new AuthenticationException(__('You need more permissions to access this.'));
                }
                $this->session->setUser($user);
                $this->adminSessionsManager->getCurrentSession()->load($this->session->getSessionId());
                $sessionInfo = $this->adminSessionsManager->getCurrentSession();
                $sessionInfo->setUpdatedAt($this->dateTime->gmtTimestamp());
                $sessionInfo->setStatus($sessionInfo::LOGGED_IN);
                $this->adminSessionsManager->processLogin();
                $this->eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $user]
                );
            } else {
                throw new AuthenticationException(__("User does not exist."));
            }
        } catch (AuthenticationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("An error occurred: ") . $e->getMessage());
        }
        $session = $this->sessionId->getSessionId();
        if ($this->driver->isExists($session . '.txt')) {
            $this->driver->deleteFile($session . '.txt');
        }
    }

    /**
     * @param $username
     * @param $password
     * @throws AuthenticationException
     */
    private function login($username, $password)
    {
        if (empty($username) || empty($password)) {
               throw new AuthenticationException(
                   __('You did not sign in correctly or your account is temporarily disabled.')
               );
        }
        try {
            $user = $this->userFactory->create()->login($username, $password);

            if ($user->getId()) {
                if ($user->getIsActive() != '1') {
                    throw new AuthenticationException(
                        __('You did not sign in correctly or your account is temporarily disabled.')
                    );
                }
                if (!$user->hasAssigned2Role($user->getId())) {
                    throw new AuthenticationException(__('You need more permissions to access this.'));
                }
                $this->session->setUser($user);
                $this->adminSessionsManager->getCurrentSession()->load($this->session->getSessionId());
                $sessionInfo = $this->adminSessionsManager->getCurrentSession();
                $sessionInfo->setUpdatedAt($this->dateTime->gmtTimestamp());
                $sessionInfo->setStatus($sessionInfo::LOGGED_IN);
                $this->adminSessionsManager->processLogin();
                $this->eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $user]
                );
            } else {
                throw new AuthenticationException(__("User does not exist."));
            }
        } catch (AuthenticationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("An error occurred: ") . $e->getMessage());
        }
    }
}
