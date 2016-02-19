<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Data\Type;

/**
 * JSON data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Json implements TypeInterface
{

    /**
     * Parse the data into PHP.
     *
     * @param  string $string
     * @param  array  $options
     * @return mixed
     */
    public static function unserialize($string, array $options = [])
    {
        $assoc = (isset($options['assoc'])) ? (bool)$options['assoc'] : true;
        $depth = (isset($options['depth'])) ? (int)$options['depth']  : 512;
        return json_decode($string, $assoc, $depth);
    }

    /**
     * Convert the data into its native string format
     *
     * @param  mixed $data
     * @param  array $options
     * @return string
     */
    public static function serialize($data, array $options = [])
    {
        $options = (isset($options['options'])) ? $options['options'] : JSON_PRETTY_PRINT;
        return json_encode($data, $options);
    }

    /**
     * Determine if the string is valid JSON
     *
     * @param  string $string
     * @return boolean
     */
    public static function isValid($string)
    {
        $json = @json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}
