<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Data\Type;

/**
 * YAML data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Yaml implements TypeInterface
{

    /**
     * Parse the string into a PHP array
     *
     * @param  string $string
     * @param  array  $options
     * @return array
     */
    public static function unserialize($string, array $options = [])
    {
        $pos = (isset($options['pos'])) ? (int)$options['pos'] : 0;
        return yaml_parse($string, $pos);
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
        $encoding = (isset($options['encoding'])) ? $options['encoding'] : YAML_UTF8_ENCODING;
        return yaml_emit($data, $encoding);
    }

    /**
     * Determine if the string is valid YAML
     *
     * @param  string $string
     * @return boolean
     */
    public static function isValid($string)
    {
        $yaml = @yaml_parse($string);
        return (($yaml !== false) && is_array($yaml));
    }

}
