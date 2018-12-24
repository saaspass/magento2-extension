<?php

namespace Saaspass\Login\Api;

interface LoginManagementInterface
{
 /**
  * Updates the specified user with the specified message.
  *
  * @api
  * @param string $session customers session_id
  * @return string
  */
    public function authenticated($session);

    /**
     * Receiving post request from Authentication Source.
     *
     * @api
     * @params string
     * @return void
     */
    public function authenticate();
}
