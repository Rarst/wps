# wps — WordPress plugin for whoops
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Rarst/wps/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Rarst/wps/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/rarst/wps/v/stable)](https://packagist.org/packages/rarst/wps)
[![Total Downloads](https://poser.pugx.org/rarst/wps/downloads)](https://packagist.org/packages/rarst/wps)
[![PHP version](https://img.shields.io/packagist/php-v/rarst/wps.svg)](https://packagist.org/packages/rarst/wps)
[![Download wps](https://img.shields.io/badge/dynamic/json.svg?label=download&url=https://api.github.com/repos/rarst/wps/releases/latest&query=$.assets%5B0%5D.name)](https://www.rarst.net/download/wps)

wps adds [whoops](http://filp.github.io/whoops/) error handler to a WordPress installation. 

It makes error messages from PHP, `admin-ajax.php`, and WP REST API a _great_ deal more clear and convenient to work with.

## Installation

| [Composer](https://getcomposer.org/) (recommended) | Release archive |  
| -------------------------------------------------- | -------- |  
| `composer require rarst/wps` | [![Download wps](https://img.shields.io/badge/dynamic/json.svg?label=download&url=https%3A%2F%2Fapi.github.com%2Frepos%2Frarst%2Fwps%2Freleases%2Flatest&query=%24.assets[0].name&style=for-the-badge)](https://www.rarst.net/download/wps) |

Alternative:

1. Clone this repository in your wp-content/plugins/ folder
2. Run `composer install` within the wp-content/plugins/wps/ folder
3. Activate the wps plugin

## Usage

The plugin is meant strictly for development and will only work with `WP_DEBUG` and `WP_DEBUG_DISPLAY` configuration constants enabled.

To temporarily disable whoops output you can use `?wps_disable` query argument in the URL. 

## License

MIT
