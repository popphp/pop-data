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
 * XML data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
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
     * Convert the data into its native format
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

        $xml      = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"{$encoding}\"?><{$root}></{$root}>", LIBXML_NOENT);

        function toXml($data, $node, $cdata, &$xml) {
            foreach($data as $key => $value) {
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
                        if ($cdata) {
                            $xml->addChild($key, '[{pop}]<![CDATA[' . str_replace('&', '&amp;', $value) . ']]>[{/pop}]');
                        } else {
                            $xml->addChild($key, htmlentities($value, ENT_QUOTES, 'UTF-8'));
                        }
                    } else {
                        $xml->addChild($key, $value);
                    }
                }
            }
        }

        toXml($data, $node, $cdata, $xml);

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->substituteEntities = false;

        // Clean entities
        $xmlString = $xml->asXML();

        $matches = [];
        preg_match_all('/\[\{pop\}\](.+?)\[\{\/pop\}\]/s', $xmlString, $matches);
        if (isset($matches[0]) && isset($matches[1])) {
            foreach ($matches[0] as $i => $match) {
                if (strpos($match, 'CDATA') !== false) {
                    $xmlString = str_replace([$match, ' & '], [html_entity_decode($matches[1][$i], ENT_QUOTES, 'UTF-8'), ' &amp; '], $xmlString);
                }
            }
        }

        $dom->loadXML($xmlString);
        return $dom->saveXML();
    }

}
