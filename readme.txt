=== Gateway for PayLate on WooCommerce ===
Contributors: kaggdesign
Donate link: https://www.paypal.me/kagg
Tags: woocommerce, gateway, paylate
Requires at least: 4.4
Tested up to: 6.4
Requires PHP: 5.6
Stable tag: 1.5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gateway for PayLate on WooCommerce provides WooCommerce payments via PayLate service.

== Description ==

Gateway for PayLate on WooCommerce is a WooCommerce payment gateway, which allows user to make payments via PayLate service.

Plugin has options page on the backend, to setup required connection parameters provided by PayLate service. Please see screenshots.

Plugin provides

* Full integration with WooCommerce as payment gateway
* Buy Button to send the order to PayLate
* Widget to show popup window with explanation of PayLate service

To create a Buy Button, use the following shortcode:

[paylate_buy_button name="Product name" price="3000" count="1"]

where

"name" is product name, "price" is product price, and "count" is a number of such products to buy.

These parameters are required for shortcode. There are also additional parameters can be used with the shortcode:

"category" as product category, "fio" as full name of the buyer, "passport" as passport data of the buyer, and "type" as button type (0-3).

Example:

[paylate_buy_button name="Product name" price="5000" count="7" category="TV" fio="Petr Ivanovich Sidorov" passport="50 50 123456" type="2"]

To create a Widget, use the following shortcode:

[paylate_widget]

Shortcode has optional parameters

[paylate_widget class="my-class" data-button="2"]

where

"class" is a CSS class to be added to the widget, and "data-button" is type of the button provided by PayLate (1-4)

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater (PHP 8.0 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 8.1 or greater is recommended)
* WooCommerce 3.0 or greater

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself, and you don’t need to leave your web browser. To do an automatic install of Gateway for PayLate on WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Gateway for PayLate on WooCommerce” and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the [Gateway for PayLate on WooCommerce Plugin Forum](https://wordpress.org/support/plugin/woo-paylate).

== Screenshots ==

1. The Gateway for PayLate on WooCommerce settings panel.
2. Checkout page.

== Changelog ==

= 1.5.3 =
* Tested with WordPress 6.4
* Tested with WooCommerce 8.4
* Fixed duplicated submenu.

= 1.5.2 =
* Freemius library removed
* Tested with WordPress 6.0
* Tested with WooCommerce 6.5

= 1.4 =
* Fixed security issue in Freemius SDK
* Tested with WorPress 5.5
* Tested with WooCommerce 4.7

= 1.3.3 =
* Tested with WorPress 5.3

= 1.3.2 =
* Tested with WorPress 5.3
* Minimal php version bumped up to 5.6

= 1.3.1 =
* Fixed security issue of Freemius SDK

= 1.3.0 =
* Tested with WordPress 5.1
* Default https port is set to 443

= 1.2 =
* Tested with WooCommerce 3.5

= 1.0 =
* Initial release.
