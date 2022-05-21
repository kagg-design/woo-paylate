# WordPress Plugin WooCommerce PayLate

WooCommerce payment gateway, which allows user to make payments via PayLate service.

![](./assets/banner-772x250.png)

## Contents

The WordPress Plugin WooCommerce PayLate includes the following files:

* `.gitignore`. Used to exclude certain files from the repository.
* `CHANGELOG.md`. The list of changes to the core project.
* `README.md`. The file that you’re currently reading.
* A `plugin-name` directory that contains the source code - a fully executable WordPress plugin.

## Features

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

* The WooCommerce PayLate is based on the [Plugin API](http://codex.wordpress.org/Plugin_API), [Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards), and [Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/).
* The WooCommerce PayLate is based on the [WooCommerce Gateway API](https://docs.woocommerce.com/document/payment-gateway-api/).
* All classes, functions, and variables are documented so that you know what you need to be changed.
* The WooCommerce PayLate uses a strict file organization scheme that correspond both to the WordPress Plugin Repository structure, and that make it easy to organize the files that compose the plugin.
* The project includes a `.pot` file as a starting point for internationalization.

## Installation

The WooCommerce PayLate can be installed directly into your plugins folder "as-is".

## WordPress.org Preparation

The original launch of this version of the WooCommerce PayLate included the folder structure needed for using your plugin on the WordPress.org. That folder structure has been moved to its own repo here: https://github.com/kagg/

## Recommended Tools

### i18n Tools

The WordPress Plugin WooCommerce PayLate uses a variable to store the text domain used when internationalizing strings throughout the WooCommerce PayLate. To take advantage of this method, there are tools that are recommended for providing correct, translatable files:

* [Poedit](https://poedit.net/)
* [makepot](http://i18n.svn.wordpress.org/tools/trunk/)
* [i18n](https://github.com/grappler/i18n)

Any of the above tools should provide you with the proper tooling to internationalize the plugin.

## License

The WordPress Plugin WooCommerce PayLate is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `LICENSE`.

## Important Notes

### Licensing

The WordPress Plugin WooCommerce PayLate is licensed under the GPL v2 or later; however, if you opt to use third-party code that is not compatible with v2, then you may need to switch to using code that is GPL v3 compatible.

For reference, [here's a discussion](https://make.wordpress.org/themes/2013/03/04/licensing-note-apache-and-gpl/) that covers the Apache 2.0 License used by [Bootstrap](http://getbootstrap.com/2.3.2/).

# Credits

The current version of the WooCommerce PayLate was developed by [KAGG Design](https://kagg.eu/en/).

## Documentation, FAQs, and More

If you’re interested in writing any documentation or creating tutorials please [let me know](https://kagg.eu/en/).
