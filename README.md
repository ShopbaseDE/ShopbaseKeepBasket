# ShopbaseKeepBasket
ShopbaseKeepBasket is a plugin for Shopware 5.4 and higher which allows the customer to keep his basket on logout.

## Required
Shopware 5.4 or higher is required

## Important informations

>Do not install the plugin in live productive mode.

The plugin will completely clear the DB-Table 's_order_basket' on install and uninstall processes.
This is required to avoid problems with old data. All basket items of users which are not ordered will lost.
