<?php
/**
 * 
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT MIT
 * 
 */


namespace RdPostOrder\App\Libraries;

if (!class_exists('\\RdPostOrder\\App\\Libraries\\Debug')) {
    /**
     * Debug class.
     */
    class Debug
    {


        /**
         * Write debug log.
         * 
         * @param mixed $message
         */
        public static function writeLog($message)
        {
            if (
                (defined('WP_DEBUG') && WP_DEBUG === true) || 
                (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true)
            ) {
                if (is_array($message) || is_object($message)) {
                    error_log(print_r($message, true));
                } else {
                    error_log($message);
                }
            }
        }// writeLog


    }
}