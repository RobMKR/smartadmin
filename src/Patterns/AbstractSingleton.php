<?php
namespace MgpLabs\SmartAdmin\Patterns;

abstract class AbstractSingleton
{
    /**
     * Singleton instance reference
     *
     * @var array
     */
    private static $_aInstance = [];

    /**
     * Private constructor
     *
     * AbstractSingleton constructor.
     */
    private function __construct() {}

    /**
     * Get Singleton Instance
     *
     * @return mixed
     */
    public static function getInstance() {

        $sClassName = get_called_class();

        if( !isset( self::$_aInstance[ $sClassName ] ) ) {

            self::$_aInstance[ $sClassName ] = new $sClassName();
        }
        $oInstance = self::$_aInstance[ $sClassName ];

        return $oInstance;
    }

    /**
     * Disable __clone method
     */
    final private function __clone() {}
}