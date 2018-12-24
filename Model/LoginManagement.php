<?php

namespace Saaspass\Login\Model;

use Saaspass\Login\Api\LoginManagementInterface as ApiInterface;
use Saaspass\Login\Helper\Data;
use Magento\Framework\Filesystem\Driver\File;

class LoginManagement implements ApiInterface
{
    private $helper;
    private $driver;
    /**
     * LoginManagement constructor.
     * @param File $driver
     * @param Data $data
     */
    public function __construct(File $driver, Data $data)
    {
        $this->driver = $driver;
        $this->helper = $data;
    }
    /**
     * @param string $session
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function authenticated($session)
    {
        if ($this->driver->isExists($session.'.txt')) {
            return "ready";
        } else {
            return 'not_ready';
        }
    }
    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function authenticate()
    {
        $headers = apache_request_headers();
        if (array_key_exists('username', $headers) && array_key_exists('tracker', $headers)
            && array_key_exists('session', $headers)) {
            $username=$headers['username'];
            $session=$headers['session'];
            $trackerID=$headers['tracker'];
            if ($this->helper->checkTrackerId($trackerID, $username) == 200) {
                $this->driver->filePutContents($session . '.txt', $username);
            }
        }
    }
}
