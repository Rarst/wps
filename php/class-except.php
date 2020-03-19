<?php
namespace Rarst\wps;

use InvalidArgumentException;

class Except {
    public $pluginsDirectories;
    public $themesDirectories;

    /**
     * Creates Object of Watch Class
     *
     * @param array $pluginsDirectories Names of plugin folders to watch for
     *                                  Notices & Warnings
     * @param array $themesDirectories Names of theme folders to watch for
     *                                 Notices and Warnings
     */
    public function __construct($pluginsDirectories = [], $themesDirectories = [])
    {
        if(!is_array($pluginsDirectories)) {
            throw new InvalidArgumentException('$pluginsDirectories should be an array');
        }

        if(!is_array($themesDirectories)) {
            throw new InvalidArgumentException('$themesDirectories should be an array');
        }

        $this->pluginsDirectories = $pluginsDirectories;
        $this->themesDirectories = $themesDirectories;
    }

    /**
     * Constructor 
     */
    public static function pluginsDirectories($plugins) {
        $plugins = func_get_args();
        
        if(empty($plugins)) {
            throw new InvalidArgumentException('Pass plugins folder names to watch for Notices & Warnings');
        }

        return new self($plugins, []);
    }

    /**
     * Constructor
     */
    public static function themesDirectories() {
        $themes = func_get_args();
        
        if(empty($themes)) {
            throw new InvalidArgumentException('Pass themes folder names to watch for Notices & Warnings');
        }
        return new self([], $themes);
    }

    public function empty() {
        return empty($this->pluginsDirectories) && empty($this->themesDirectories);
    }

    public function emptyPlugins() {
        return empty($this->pluginsDirectories);
    }

    public function emptyThemes() {
        return empty($this->themesDirectories);
    }
}