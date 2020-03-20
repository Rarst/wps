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

## Usage

The plugin is meant strictly for development and will only work with `WP_DEBUG` and `WP_DEBUG_DISPLAY` configuration constants enabled.

To temporarily disable whoops output you can use `?wps_disable` query argument
in the URL. 

By default the plugin blocks the execution whenever any kind of
notice/warning/error/exception occurs. This is really useful when
site is being built from the scratch, however, this could create a problem 
when everything is not being built from the scratch — specifically when a notice
or warning comes from a plugin you did not develop.

This could be solved in one of the four ways.

### Skip Whoops Blocking for all Notices and Warnings
In `wps.php`, call `skipNoticesAndWarnings()` on `$wps` object. So it
might look like this
```diff
  $wps = new \Rarst\wps\Plugin();
+ $wps->skipNoticesAndWarnings();
  $wps->run();
```

`skipNoticesAndWarnings` accepts an object of `Rarst\wps\Except` class to
enable Whoops for notices/warnings of only specified plugins/themes.

### Enable Whoops' Notices/Warnings Blocking for Specific Plugins
```diff
  $wps = new \Rarst\wps\Plugin();
+ $wps->skipNoticesAndWarnings(Rarst\wps\Except::pluginsDirectories('akismet'));
  $wps->run();
```
Above example will skip all notices and warnings except coming from `akismet`
plugin. Pass multiple plugins to be excepted as separate arguments.

### Enable Whoops Notices/Warnings Blocking Blocking for Specific Themes
```diff
  $wps = new \Rarst\wps\Plugin();
+ $wps->skipNoticesAndWarnings(Rarst\wps\Except::themesDirectories('twentytwenty'));
  $wps->run();
```
Above example will skip all notices and warnings except coming from `twentytwenty`
theme. Pass multiple themes to be excepted as separate arguments.

### Enable Whoops Blocking for Specific Plugins and Themes together
```diff
  $wps = new \Rarst\wps\Plugin();
+ $excluded_plugins = ['akismet'];
+ $excluded_themes  = ['twentytwenty'];
+ $wps->skipNoticesAndWarnings(new Rarst\wps\Except($excluded_plugins, $excluded_themes));
  $wps->run();
``` 
### Silent Errors using Regex
By calling `silenceErrorsInPaths` on `$wps` object in `wps.php` file
before `$wps->run()`, one can define regex paths to silent errors.

While the first parameter of silenceErrorsInPaths accepts regex path,
the second parameter accepts error levels to silent. Multiple paths can
be passed through an array.

e.g. If you always want to silence errors coming from vendor directories,
writing something like below in wps.php will work
```diff
  $wps = new \Rarst\wps\Plugin();
+ $wps->silenceErrorsInPaths('@/vendor@', E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE);
  $wps->run();
```
Another Example -
If you want to allow notices/warnings from specific plugin but at the
same time silent errors that are coming from vendor directory which
is inside the plugin, then you might write something like this in
wps.php.
```diff
  $wps = new \Rarst\wps\Plugin();
+ $wps->skipNoticesAndWarnings(Rarst\wps\Except::pluginsDirectories('plugin-folder'));
+ $wps->silenceErrorsInPaths('@plugin-folder/vendor@', E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE);
  $wps->run();
```

## License

MIT