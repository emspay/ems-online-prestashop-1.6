# Changelog Prestashop 1.6

** 1.7.1 **

* Updated validation

** 1.7.0 **

* Added AfterPay Payment Method

* Aligned description field display & json

* Fix for the cart double validation issue

* Added new Sofort & Klarna Logos

* Other minor bug fixes


** 1.6.1**


* Updated ing-php library to version 1.4.0 


** 1.6.0 **

* Added Payconiq Payment Method

* Added missing webhook mapping

* Updated translations

* Synced ing-php library

* Updated changelog with latest info and correct format


** 1.5.0 **

* Fixed folders

* Added pipelines config

* Removing .gitignore & adding missing libraries

* Support updating klarna order to "shipped"

* Removing hardcoded shipping state Id.

* Removing hardcoded payment method name.


** 1.4.5 **

* Improved iDEAL error handling functionality for requesting issuers list

* Improved Klarna error handling and order line collection

* Added updated translations to Prestashop

* Readme.md - Added Pre-requisites to install text

* Added new method for getting the plugin version.

* Code fixes


** 1.4.4 **

* Added translations for French, Dutch, German and English

* Improved Error handling for all payment methods

* Fixed configuration issues

* Fixed Typo in Template


** 1.4.3 **

* Added locale to Bancontact

* Fixed locale issues

** 1.4.2 ** 

* Updated ing-php library to v1.2.8

* Some minor fixes


** 1.4.1 **

* Added Klarna IP filtering functionality

* Code cleanup and re-factoring


** 1.4.0 **

* Updated trnaslations, updated templates, updated order line handling

* Updated ing-php library to include Klarna, Paypal, HomePay and Sofort

* Merging Klarna related items into Presta shop plugin settings

* Fixed German Translations

* Updated ing-php library to 1.2.5

* Updated CHANGELOG


** 1.3.5 **

* Add more payment methods logos

* Added support for German Translations

* Added Klarna payment method to Prestashop

* Added more logging on the webhook


** 1.3.4 **

* Fixes not updating the order id for Bancontact


** 1.3.3 **

* Added Bancontact, SOFORT, Klarna and Home'Pay to the mapping


** 1.3.2 **

* Removed outdated documentation

* Added SOFORT, PayPal, HomePay,

* Fixed SOFORT

* Added translations

* Fixed issue with missing merchant_order_id in the webhook


** 1.3.1 ** 

* Fixed typo in webhook path, updated config.xml records


** 1.3.0 ** 

* Used the new logo

* Updated Bancontact logo

* Code improvements and fixes

* Removed old library

* Refactored Cash on Delivery, Bank Tansfer, Credit Cards, Main modules

* Fixed typo in the module name

* Fixes for Bancontact

* Removed debug code from Client.php

* Added ePay to dropdown

* Fixed Uncaught exception error

* Hide modules which are not allowed

* Added Ajax processing page

* Translations and locale updates

* Replace ginger-php with ing-php library

* Added a pending page when transaction is still being processed after 60 seconds

* Added ING checkout manual

* Updated ing-php library to latest version

* Added Webhook URL option and CA file bundle

* Added missing check for empty API key and Webhook URL 

* Catching thrown exceptions in API KEY settings

* Added Error page when payment failed during checkout


** 1.1.4 **

* Fixed Bancontact naming


** 1.1.3 **

* Send in locale

* Updated changelog


** 1.1.2 **

* Use base dir to reference to images, depending on the way installed a relative path won't work

* Added gitignore


** 1.0.9 **

* Add locale to the order, this results in the payment page switching to the right language

* synced with current version

* Added locale to creditcard call

* hack to prevent en_NL to be send in


** 1.1.1 **

* Updated the manual

* Rename to ING PSP instead of ING KassaCompleet

* Moved selection of the endpoint to the lib based upon the selected product

* Added Bancontact

* Added payment methods to checkout

* Added Translations

* Used different class name to prevent two versions deployed at the same time


** 1.0.8 **

* Updated the documentation

* Replaced array_filter by array_values preventing JSON errors


** 1.0.7 ** 

* Added cash-on-delivery to the plugin

* Updated translations


** 1.0.6 **

* Update the README with instructions on how to handle order_id not appearing in KC UI

* Added some output to web hook to be able to debug more easily if something goes wrong

* Also send in plugin version when calling the API

* Updated Changelog


** 1.0.5 ** 

* Fixed issue for failing webhook


** 1.0.4 **

* For bank-transfer; process feedback directly

* Fixed space issue with translations

* Added more strings to the translastion system


** 1.0.2 **

* Added documentation

* Removed banktransfer logic from cc method


** 1.0.1 **

* Initial Version

* Added implementation for Bank Transfer, Ideal, Credit Card

* Code Improvements

* Added translations

* Bug Fixes

* Added webhook & Return URL

* Updated readme

* Updated changelog

* Added htaccess to protect downloads dir, renamed install for conformity