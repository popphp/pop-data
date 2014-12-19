<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Data\Type;

/**
 * Data type interface
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
interface TypeInterface
{

    /**
     * Parse the string into a PHP array
     *
     * @param  string $string
     * @param  array  $options
     * @return array
     */
    public static function unserialize($string, array $options = []);

    /**
     * Convert the data into its native format
     *
     * @param  mixed $data
     * @param  array $options
     * @return string
     */
    public static function serialize($data, array $options = []);

}
