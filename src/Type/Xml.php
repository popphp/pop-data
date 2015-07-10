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
 * XML data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Xml implements TypeInterface
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
        $matches = [];
        preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches);

        foreach ($matches[0] as $match) {
            $strip = str_replace(
                ['<![CDATA[', ']]>', '<', '>'],
                ['', '', '&lt;', '&gt;'],
                $match
            );
            $string = str_replace($match, $strip, $string);
        }

        return json_decode(json_encode((array)simplexml_load_string($string)), true);
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
        $root     = (isset($options['root']))     ? $options['root']        : 'data';
        $node     = (isset($options['node']))     ? $options['node']        : 'row';
        $encoding = (isset($options['encoding'])) ? $options['encoding']    : 'utf-8';
        $cdata    = (isset($options['cdata']))    ? (bool)$options['cdata'] : false;
        $pma      = (isset($options['pma']))      ? (bool)$options['pma']   : false;

        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"{$encoding}\"?><{$root}></{$root}>", LIBXML_NOENT);

        // Format by table/column style for phpMyAdmin
        if ($pma) {
            foreach ($data as $name => $values) {
                if (!is_numeric($name) && is_array($values)) {
                    foreach ($values as $value) {
                        $table = $xml->addChild('table');
                        $table->addAttribute('name', $name);
                        foreach ($value as $key => $val) {
                            if ((strpos($val, '<') !== false) || (strpos($val, '&') !== false)) {
                                $val = ($cdata) ?
                                    '[{pop}]<![CDATA[' . str_replace('&', '&amp;', $val) . ']]>[{/pop}]' :
                                    htmlentities($val, ENT_QUOTES, 'UTF-8');
                            }
                            $column = $table->addChild('column', $val);
                            $column->addAttribute('name', $key);
                        }
                    }
                }
            }
        // Else, format normally
        } else {
            /**
             * Function to recursively crawl through the array and create the XML nodes
             *
             * @param mixed $data
             * @param mixed $node
             * @param mixed $cdata
             * @param mixed $xml
             */
            function toXml($data, $node, $cdata, &$xml)
            {
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        if (!is_numeric($key)) {
                            $subNode = $xml->addChild($key);
                            toXml($value, $node, $cdata, $subNode);
                        } else {
                            $subNode = $xml->addChild($node);
                            toXml($value, $node, $cdata, $subNode);
                        }
                    } else {
                        if ((strpos($value, '<') !== false) || (strpos($value, '&') !== false)) {
                            $value = ($cdata) ?
                                '[{pop}]<![CDATA[' . str_replace('&', '&amp;', $value) . ']]>[{/pop}]' :
                                htmlentities($value, ENT_QUOTES, 'UTF-8');
                        }
                        $xml->addChild($key, $value);
                    }
                }
            }

            toXml($data, $node, $cdata, $xml);
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->substituteEntities = false;

        // Clean entities
        $xmlString = $xml->asXML();
        $matches   = [];
        preg_match_all('/\[\{pop\}\](.+?)\[\{\/pop\}\]/s', $xmlString, $matches);
        if (isset($matches[0]) && isset($matches[1])) {
            foreach ($matches[0] as $i => $match) {
                if (strpos($match, 'CDATA') !== false) {
                    $xmlString = str_replace(
                        [$match, ' & '],
                        [html_entity_decode($matches[1][$i], ENT_QUOTES, 'UTF-8'), ' &amp; '],
                        $xmlString
                    );
                }
            }
        }

        $dom->loadXML($xmlString);
        return $dom->saveXML();
    }

    /**
     * Determine if the string is XML
     *
     * @param  string $string
     * @return boolean
     */
    public static function isValid($string)
    {
        libxml_use_internal_errors(true);
        return (@simplexml_load_string($string));
    }

}
