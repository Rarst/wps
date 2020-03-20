<?php
namespace Rarst\wps;

use InvalidArgumentException;

class Except {
    private $pathPatterns;

    /**
     * Constructor
     *
     * @param  array|string $patterns List or a single regex pattern to match
     */
    public function __construct($pathPatterns) {
        $pathPatterns = func_get_args();

        if(is_array($pathPatterns[0])) {
            $pathPatterns = $pathPatterns[0];
        }
        
        $this->pathPatterns = array_filter($pathPatterns);
    }

    /**
     * Constructor
     * 
     * @param array $pluginsDirectories Names of plugin folders to watch for
     *                                  Notices & Warnings.
     */
    public static function pluginsDirectories($plugins) {
        $plugins = func_get_args();
        
        if(empty($plugins)) {
            throw new InvalidArgumentException('Pass plugins folder names to watch for Notices & Warnings');
        }

        $plugins = array_map(function($plugin){
            return '@plugins/' . preg_quote($plugin, '@') . '@';
        }, $plugins);

        return new self($plugins);
    }

    /**
     * Constructor
     * 
     * @param array $themesDirectories Names of theme folders to watch for
     *                                 Notices and Warnings.
     */
    public static function themesDirectories() {
        $themes = func_get_args();
        
        if(empty($themes)) {
            throw new InvalidArgumentException('Pass themes folder names to watch for Notices & Warnings');
        }

        $themes = array_map(function($theme){
            return '@themes/' . preg_quote($theme, '@') . '@';
        }, $themes);

        return new self($themes);
    }

    /**
     * Constructor
     */
    public static function blank() {
        return new self([]);
    }

    public function empty() {
        return empty($this->pathPatterns);
    }

    public function pathPatterns() {
        return $this->pathPatterns;
    }
}