<?php
namespace Rarst\wps;

use Whoops\Exception\ErrorException;
use Whoops\Exception\Inspector;
use Whoops\Run;
use Whoops\Util\SystemFacade;

/**
 * This class will show Errors and Warnings only if they are coming from
 * Specific Plugin
 */
class Whoops_Run_Composite {

    /**
     * @var SystemFacade
     */
    private $system;
    
    /**
     * @var Run
     */
    private $run;
    
    private $skipAllNoticesAndWarnings = false;

    private $watchSpecificPlugins = [];

    private $watchSpecificThemes = [];

    public function __construct(SystemFacade $system = null)
    {
        $this->system = $system ?: new SystemFacade;
        $this->run = new Run($this->system);
    }

    public function register() {
        class_exists("\\Whoops\\Exception\\ErrorException");
        class_exists("\\Whoops\\Exception\\FrameCollection");
        class_exists("\\Whoops\\Exception\\Frame");
        class_exists("\\Whoops\\Exception\\Inspector");

        $this->system->setErrorHandler([$this, Run::ERROR_HANDLER]);
        $this->system->setExceptionHandler([$this->run, Run::EXCEPTION_HANDLER]);
        $this->system->registerShutdownFunction([$this->run, Run::SHUTDOWN_HANDLER]);
    }

    public function unregister() {
        $this->system->restoreExceptionHandler();
        $this->system->restoreErrorHandler();
    }

    public function pushHandler($handler) {
        $this->run->pushHandler($handler);
    }

    public function skipAllNoticesAndWarnings() {
        $this->skipAllNoticesAndWarnings = true;
    }

    public function watchSpecificPlugins($plugins) {
        $this->watchSpecificPlugins = $plugins;
    }

    public function watchSpecificThemes($themes) {
        $this->watchSpecificThemes = $themes;
    }

    /**
     * Silence particular errors in particular files
     * @param  array|string $patterns List or a single regex pattern to match
     * @param  int          $levels   Defaults to E_STRICT | E_DEPRECATED
     * @return Run
     * @see https://maximivanov.github.io/php-error-reporting-calculator/
     */
    public function silenceErrorsInPaths($patterns, $levels = 10240) {
        $this->run->silenceErrorsInPaths($patterns, $levels);
    }

    /**
     * Converts generic PHP errors to \ErrorException
     * instances, before passing them off to be handled.
     *
     * This method MUST be compatible with set_error_handler.
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return bool
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = null, $line = null) {

        // Handle all fatals, exceptions etc.
        if(in_array(
            $level, 
            [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR]
        )) {
            $this->run->handleError($level, $message, $file, $line);
            return false;
        }

        // If All Notices and Warnings should be skipped, then short-circuit
        // error handler.
        if($this->skipAllNoticesAndWarnings) {
            return false;
        }

        // Watch for all plugins and Themes
        if( empty($this->watchSpecificPlugins) && empty($this->watchSpecificThemes ) ) {
            $this->run->handleError($level, $message, $file, $line);
            return false;
        }

        $watchablePlugins = empty($this->watchSpecificPlugins) ? [] : array_map(function($pluginBaseFolder){
            return 'plugins/' . $pluginBaseFolder;
        }, $this->watchSpecificPlugins);

        $watchableThemes = empty($this->watchSpecificThemes) ? [] : array_map(function($themeBaseFolder){
            return 'themes/' . $themeBaseFolder;
        }, $this->watchSpecificThemes);

        $directoriesToWatchFor = array_merge($watchablePlugins, $watchableThemes);
        $errorBelongsToWatchCriteria = false;
        
        // We are creating an exception object because Inspector Class needs it.
        $exception = new ErrorException($message, /*code*/ $level, /*severity*/ $level, $file, $line);
        $frames = (new Inspector($exception))->getFrames();
        foreach( $frames as $frame ) {
            foreach($directoriesToWatchFor as $directory) {
                if (strpos($frame->getFile(), $directory) !== false) {
                    $errorBelongsToWatchCriteria = true;
                    break 2;
                }
            }
        }

        if($errorBelongsToWatchCriteria) {
            $this->run->handleError($level, $message, $file, $line);
        }

        return false;
    }
}