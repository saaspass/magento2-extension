![SAASPASS Logo](https://saaspass.com/_next/static/assets/3aa373afc80ce9b11762ebde7047c2fa.png)
# Magento 2 Admin – SAASPASS integration (MFA)

Magento Extension that allows the capability to integrate SAASPASS Multi-Factor Authentication and enable secure login within the Magento Store for Administrators.

## Magento versions support

* Compatible with Magento 2.0+

### How does it work?

When enabled, SAASPASS module adds new authentication methods which allow administrators to login securely. Once enabled and configured, administrators will be able to use one of the following login methods in addition to the user credentials: 

1. Barcode
2. One-time password
3. Push Login

### Usage

1. You are required prior to installation to sign up to SAASPASS with your company email.
2. Create the Magento application from within the SAASPASS Admin Web Portal;
https://www.saaspass.com/sd/#/login
	- Note: after you login you need to use Switch button on the top to switch to your Company Admin mode.
3. Install the module via composer running “composer require saaspass/module”.
4. Enable the installed module.
5. Login to you backend and navigate to “Store→Configuration→SAASPASS Configuration”
6. You will be required to set your Application Key and Application Password 
	- Application Key and Application Password can be found under Integration section under the Manage Applications inside Magento created application within SAASPASS Company Portal.
7. After finishing with configuration you need to assign the correct users or a group of users to your Magento Application in the SAASPASS Admin Web Portal under User Directories.