<?php
/*
Plugin Name: wps
Plugin URI: https://github.com/Rarst/wps
Description: WordPress plugin for Whoops error handler.
Author: Andrey "Rarst" Savchenko
Version: 
Author URI: http://www.rarst.net/
Text Domain: 
Domain Path: /lang
License: MIT

Copyright (c) 2013 Andrey "Rarst" Savchenko

Permission is hereby granted, free of charge, to any person obtaining a copy of this
software and associated documentation files (the "Software"), to deal in the Software
without restriction, including without limitation the rights to use, copy, modify, merge,
publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies
or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.*/

use Whoops\Run;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
	return;
}

if ( ! defined( 'WP_DEBUG_DISPLAY' ) || false === WP_DEBUG_DISPLAY ) {
	return;
}

$whoops = new Run;

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	$json_handler = new JsonResponseHandler();
	$json_handler->addTraceToOutput( true );
	$whoops->pushHandler( $json_handler );
	$whoops->register();
	return;
}

$whoops_handler = new PrettyPageHandler;

$whoops_handler->addDataTableCallback( 'WP', function () {
	global $wp;

	if ( ! $wp instanceof \WP ) {
		return array();
	}

	$output = get_object_vars( $wp );
	unset( $output['private_query_vars'] );
	unset( $output['public_query_vars'] );

	return array_filter( $output );
} );

$whoops_handler->addDataTableCallback( 'WP_Query', function () {
	global $wp_query;

	if ( ! $wp_query instanceof \WP_Query ) {
		return array();
	}

	$output               = get_object_vars( $wp_query );
	$output['query_vars'] = array_filter( $output['query_vars'] );
	unset( $output['posts'] );
	unset( $output['post'] );

	return array_filter( $output );
} );

$whoops_handler->addDataTableCallback( '$post', function () {
	return get_object_vars( get_post() );
} );

$whoops_handler->setEditor(
	function ( $file, $line ) {
		return "http://localhost:8091?message={$file}:{$line}";
	}
);

$whoops->pushHandler( $whoops_handler );
$whoops->register();

ob_start(); // or we are going to be spitting out WP markup before whoops