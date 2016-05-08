<?php
namespace Rarst\wps;

use Whoops\Exception\Formatter;
use Whoops\Handler\Handler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Util\Misc;

/**
 * WordPress-specific version of Json handler.
 */
class Admin_Ajax_Handler extends JsonResponseHandler {

	/**
	 * @return bool
	 */
	private function isAjaxRequest() {

		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * @return int
	 */
	public function handle() {

		if ( ! $this->isAjaxRequest() ) {
			return Handler::DONE;
		}

		$response = array(
			'success' => false,
			'data'    => Formatter::formatExceptionAsDataArray( $this->getInspector(), $this->addTraceToOutput() ),
		);

		if ( Misc::canSendHeaders() ) {
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		}

		$json_options = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? JSON_PRETTY_PRINT : 0;

		echo wp_json_encode( $response, $json_options );

		return Handler::QUIT;
	}
}
