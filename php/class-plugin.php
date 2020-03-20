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

		// Files to watch for Notices and Warnings.
		$defaults['watch_files'] = null;

		$this['silence_errors_in_paths.pattern'] = null;
		$this['silence_errors_in_paths.levels'] = 10240; // E_STRICT | E_DEPRECATED.

		$defaults['run'] = function ( $plugin ) {
			$run = new Whoops_Run_Composite( $plugin['whoops_system_facade'] );

			if( true === $plugin['skip_all_notices_and_warnings'] ) {
				$run->skipAllNoticesAndWarnings();
			}

			if( ! is_null( $plugin['watch_files'] ) ) {
				$run->watchFilesWithPatterns( $plugin['watch_files'] );
			}

			if( ! is_null( $plugin['silence_errors_in_paths.pattern'] ) ) {
				$run->silenceErrorsInPaths(
					$plugin['silence_errors_in_paths.pattern'],
					$plugin['silence_errors_in_paths.levels']
				);
			}

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
	 * @param Except $except Directories to be excepted from this privilege.
	 * @return void
	 */
	public function skipNoticesAndWarnings(Except $except) {
		if( $except->empty() ) {
			$this['skip_all_notices_and_warnings'] = true;
			return;
		}

		$this['watch_files'] = $except->pathPatterns();
	}

	/**
     * Silence particular errors in particular files
     * @param  array|string $patterns List or a single regex pattern to match
     * @param  int          $levels   Defaults to E_STRICT | E_DEPRECATED
     * @return \Whoops\Run
     */
    public function silenceErrorsInPaths($patterns, $levels = 10240) {
		$this['silence_errors_in_paths.pattern'] = $patterns;
		$this['silence_errors_in_paths.levels'] = $levels;
	}
	
	/**
	 * Execute run conditionally on debug configuration.
	 */
	public function run() {

		if ( ! $this->is_debug() || ! $this->is_debug_display() ) {
			add_action('admin_notices', function(){
				echo '<div class="notice notice-warning is-dismissible">
						<p><strong>wps</strong> plugin works only when <code>WP_DEBUG</code> & <code>WP_DEBUG_DISPLAY</code> constants are set to <code>true</code></p>
					</div>';
			});
			return;
		}

		/** @var Run $run */
		$run = $this['run'];
		$run->register();
		ob_start(); // Or we are going to be spitting out WP markup before whoops.
	}
}
