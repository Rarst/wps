# wps — WordPress plugin for whoops
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Rarst/wps/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Rarst/wps/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/rarst/wps/v/stable)](https://packagist.org/packages/rarst/wps)
[![Total Downloads](https://poser.pugx.org/rarst/wps/downloads)](https://packagist.org/packages/rarst/wps)

wps adds [whoops](http://filp.github.io/whoops/) error handler to a WordPress installation. 

It makes error messages from PHP, `admin-ajax.php`, and WP REST API a _great_ deal more clear and convenient to work with.

## Installation

Download plugin archive from [releases section](https://github.com/Rarst/wps/releases).

Or install in plugin directory via [Composer](https://getcomposer.org/):

    composer create-project rarst/wps --no-dev

The plugin is meant strictly for development and will only work with `WP_DEBUG` and `WP_DEBUG_DISPLAY` configuration constants enabled.

To temporarily disable whoops output you can use `?wps_disable` query argument in the URL. 

## License

MIT