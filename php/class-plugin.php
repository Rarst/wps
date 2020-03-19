<?php
namespace Rarst\wps;

use Pimple\Container;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\SystemFacade;

/**
 * Main plugin's class.
 */
class Plugin extends Container {

	/**
	 * @param array $values Optional arguments for container.
	 */
	public function __construct( $values = array() ) {

		$defaults = array();

		$defaults['tables'] = array(
			'$wp'       => function () {
				global $wp;

				if ( ! $wp instanceof \WP ) {
					return array();
				}

				$output = get_object_vars( $wp );
				unset( $output['private_query_vars'] );
				unset( $output['public_query_vars'] );

				return array_filter( $output );
			},
			'$wp_query' => function () {
				global $wp_query;

				if ( ! $wp_query instanceof \WP_Query ) {
					return array();
				}

				$output               = get_object_vars( $wp_query );
				$output['query_vars'] = array_filter( $output['query_vars'] );
				unset( $output['posts'] );
				unset( $output['post'] );

				return array_filter( $output );
			},
			'$post'     => function () {
				$post = get_post();

				if ( ! $post instanceof \WP_Post ) {
					return array();
				}

				return get_object_vars( $post );
			},
		);

		$defaults['handler.pretty'] = function ( $plugin ) {
			$handler = new PrettyPageHandler();

			foreach ( $plugin['tables'] as $name => $callback ) {
				$handler->addDataTableCallback( $name, $callback );
			}

			// Requires Remote Call plugin.
			$handler->addEditor( 'phpstorm-remote-call', 'http://localhost:8091?message=%file:%line' );

			return $handler;
		};

		$defaults['handler.json'] = function () {
			$handler = new Admin_Ajax_Handler();
			$handler->addTraceToOutput( true );

			return $handler;
		};

		$defaults['handler.rest'] = function () {
			$handler = new Rest_Api_Handler();
			$handler->addTraceToOutput( true );

			return $handler;
		};

		$defaults['handler.text'] = function () {
			return new PlainTextHandler();
		};

		$defaults['whoops_system_facade'] = function() {
			return new SystemFacade;
		};

		$defaults['skip_all_notices_and_warnings'] = false;

		// Plugins to watch for Notices and Warnings. If blank, then watch for
		// notices and warnings of all plugins.
		$defaults['watch_specific_plugins'] = [];

		// Themes to watch for Notices and Warnings. If blank, then watch for
		// notices and warnings of whichever theme is active.
		$defaults['watch_specific_themes'] = [];

		$defaults['run'] = function ( $plugin ) {
			$run = new Whoops_Run_Composite( $plugin['whoops_system_facade'] );

			if( true === $plugin['skip_all_notices_and_warnings'] ) {
				$run->skipAllNoticesAndWarnings();
			}
			$run->watchSpecificPlugins( $plugin['watch_specific_plugins'] );
			$run->watchSpecificThemes( $plugin['watch_specific_themes'] );

			$run->pushHandler( $plugin['handler.pretty'] );
			$run->pushHandler( $plugin['handler.json'] );
			$run->pushHandler( $plugin['handler.rest'] );

			if ( \Whoops\Util\Misc::isCommandLine() ) {
				$run->pushHandler( $plugin['handler.text'] );
			}

			return $run;
		};

		parent::__construct( array_merge( $defaults, $values ) );
	}

	/**
	 * @return bool
	 */
	public function is_debug() {

		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * @return bool
	 */
	public function is_debug_display() {

		return defined( 'WP_DEBUG_DISPLAY' ) && false !== WP_DEBUG_DISPLAY;
	}

	/**
	 * Skip Notices and Warnings occurring while program execution
	 *
	 * @param Except $except Plugins & Themes to be excepted from this privilege.
	 * @return void
	 */
	public function skipNoticesAndWarnings(Except $except) {
		if( $except->empty() ) {
			$this['skip_all_notices_and_warnings'] = true;
			return;
		}

		if( ! $except->emptyPlugins() ) {
			$this['watch_specific_plugins'] = $except->pluginsDirectories;
		}

		if( ! $except->emptyThemes() ) {
			$this['watch_specific_themes'] = $except->themesDirectories;
		}
	}

	/**
	 * Execute run conditionally on debug configuration.
	 */
	public function run() {

		if ( ! $this->is_debug() || ! $this->is_debug_display() ) {
			return;
		}

		/** @var Run $run */
		$run = $this['run'];
		$run->register();
		ob_start(); // Or we are going to be spitting out WP markup before whoops.
	}
}
