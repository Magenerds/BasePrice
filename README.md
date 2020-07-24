# Magenerds_BasePrice

With this extension you can show the base price of volume products to your customers.   
If you have products which you sell in amounts and units this extension can calculate the base price.   
The extension installs the following basic units and its conversions:
* kg
* g
* mg
* l
* ml
* m
* cm
* mm

You can also extend the units and edit all conversions. Furthermore the display of the base price is editable. This extension also works for configurable products if you provide the necessary information for the configurable’s simple products.

## Extension installation
The easiest way to install the Magenerds module is via composer
```
# add to composer require
composer require magenerds/baseprice

# run magento setup to activate the module
bin/magento set:up
```

## Extension activation
At any time you can enable and disable this extension in the system configuration.   
You can do this by opening the backend menu ```Stores > Configuration```.   
There you will find the entry ```Magenerds > Base Price```.   
After clicking on it you see a dropdown box where you can enable/disable the module. Save the configuration and delete the cache.

![BasePrice-Activation](_images/bp_activation.png?raw=true "BasePrice Activation")

## Extension configuration
The extension can be configured if you go to ```Stores > Configuration``` and afterwards to ```Magenerds > Base Price```.   
After enabling the extension you can edit the base price template in order to influence the base price rendering in the frontend. You can type in whatever you want.   
There are three variables available to render the base price information:
* {BASE_PRICE}: Renders the calculated base price
* {REF_AMOUNT}: Renders the reference amount
* {REF_UNIT}: Renders the reference unit

**Example**: {BASE__PRICE} / {REF__AMOUNT} {REF__UNIT} will render to 2.90€ / 10 kg in the frontend.

![BasePrice-Template](_images/bp_config_1.png?raw=true "BasePrice Template")

The extension install basic units and its conversions. But you can edit these conversions, delete the units or extend it with further units and its conversions.   
Just click on Add below all those conversions in order to add another unit row. You define the product unit on the left side and the reference unit (the unit which the price will be calculated to) on the right side.

![BasePrice-Mapping](_images/bp_config_2.png?raw=true "BasePrice Mapping")

## How to use
Every product needs detailed information about the base price calculation. There are four attributes which every product has:
* Product amount: Select the amount the product gets selled with quantity 1
* Product unit: Select the unit the product gets selled with
* Reference amount: Select the reference amount the product price has to be calculated with
* Reference unit: Select the reference unit the product price has to be calculated with

**Example**: You have a product which is a bottle of milk which is 100 ml of size. It costs 2 €.   
You want to display the price of milk for 1 l which is 20 €.   
Therefore you have to configure your product like the following:
* Product amount: 100
* Product unit: ml
* Reference amount: 1
* Reference unit: l

You can configure the attributes in a product edit mask under the tab Base Price on the left side.

![BasePrice-Usage](_images/bp_use.png?raw=true "BasePrice Usage")
