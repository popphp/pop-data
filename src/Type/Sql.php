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
 * SQL data type class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Sql implements TypeInterface
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
        $data      = [];
        $fieldKeys = [];
        $curTable  = null;
        $lines     = preg_split("/((\r?\n)|(\r\n?))/", $string);

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                if (stripos($line, 'INSERT') !== false) {
                    // Has fields
                    if (stripos($line, ') VALUES') !== false) {
                        $table = substr($line, 11);
                        $table = trim(substr($table, 0, strpos($table, '(')));
                        $fields = substr($line, (strpos($line, '(') + 1));
                        $fields = substr($fields, 0, strpos($fields, ')'));
                        $fields = explode(',', $fields);
                        foreach ($fields as $key => $value) {
                            $fields[$key] = self::unquote(trim($value));
                        }
                    // Does not have fields
                    } else {
                        $table = substr($string, 11);
                        $table = trim(substr($table, 0, strpos($table, 'VALUES')));
                        $fields = null;
                    }
                    $curTable = self::unquote($table);
                    if (!isset($data[$curTable])) {
                        $data[$curTable] = [];
                    }
                    if (!isset($fieldKeys[$curTable])) {
                        $fieldKeys[$curTable] = $fields;
                    }
                } else if ((null !== $curTable) && (substr($line, 0, 1) == '(')) {
                    $line = substr($line, 1);
                    $line = substr($line, 0, -2);

                    $values = str_getcsv($line);
                    foreach ($values as $key => $value) {
                        $value = trim($value);
                        if (((substr($value, 0, 1) == '"') && (substr($value, -1) == '"')) || ((substr($value, 0, 1) == "'") && (substr($value, -1) == "'"))) {
                            $value = substr($value, 1, -1);
                        }
                        $values[$key] = stripslashes($value);
                    }

                    if (isset($fieldKeys[$curTable]) && (null !== $fieldKeys[$curTable]) && (count($fieldKeys[$curTable]) == count($values))) {
                        $data[$curTable][] = array_combine($fieldKeys[$curTable], $values);
                    } else {
                        $data[$curTable][] = $values;
                    }
                }
            }
        }

        return $data;
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
        $divide = (isset($options['divide'])) ? (int)$options['divide'] : 100;
        $quote  = (isset($options['quote']))  ? $options['quote']       : null;
        $table  = (isset($options['table']))  ? $options['table']       : 'data';

        if ((strpos($quote, '[') !== false) || (strpos($quote, ']') !== false)) {
            $quote    = '[';
            $quoteEnd = ']';
        } else {
            $quoteEnd = $quote;
        }

        $keys    = array_keys($data);
        $isAssoc = false;

        foreach ($keys as $key) {
            if (!is_numeric($key)) {
                $isAssoc = true;
            }
        }

        if (!$isAssoc) {
            $data = [$table => $data];
        }

        $sql = '';

        foreach ($data as $table => $values) {
            $table     = self::quote($table, $quote, $quoteEnd);
            $hasFields = true;
            $fields    = null;
            if (is_array($values) && isset($values[0]) && is_array($values[0])) {
                $fields = array_keys($values[0]);
                foreach ($fields as $key => $value) {
                    if (is_numeric($value)) {
                        $hasFields = false;
                    }
                    $fields[$key] = self::quote($value, $quote, $quoteEnd);
                }
                if ($hasFields) {
                    $fields = " (" . implode(', ', $fields) . ")";
                }
            }

            $sql .= "INSERT INTO " . $table . $fields . " VALUES\n";

            $i = 1;
            foreach ($values as $key => $ary) {
                foreach ($ary as $k => $v) {
                    if (!is_numeric($v)) {
                        $ary[$k] = "'" . str_replace(["'", "\n", "\r"], ["\\'", " ", " "], $v) . "'";
                    }
                }

                $sql .= "(" . implode(', ', $ary) . ")";

                if (($i % $divide) == 0) {
                    $sql .= ";\n";
                    if ($i < (count($values))) {
                        $sql .= "INSERT INTO " . $table . $fields . " VALUES\n";
                    }
                } else {
                    $sql .= ($i < (count($values))) ? ",\n" : ";\n\n";
                }
                $i++;
            }
        }

        return $sql;
    }

    /**
     * Serialize single row of data
     *
     * @param  mixed $data
     * @param  array $options
     * @return string
     */
    public static function serializeRow($data, array $options = [])
    {
        $options['divide'] = 1;
        return self::serialize([$data], $options);
    }

    /**
     * Quote the value
     *
     * @param  string $value
     * @param  string $open
     * @param  string $close
     * @return string
     */
    public static function quote($value, $open = null, $close = null)
    {
        if (strpos($value, '.') !== false) {
            $valueAry = explode('.', $value);
            foreach ($valueAry as $key => $val) {
                $valueAry[$key] = $open . $val . $close;
            }
            $quotedValue = implode('.', $valueAry);
        } else {
            $quotedValue = $open . $value . $close;
        }

        return $quotedValue;
    }

    /**
     * Unquote the value
     *
     * @param  string $value
     * @return string
     */
    public static function unquote($value)
    {
        $quote    = null;
        $quoteEnd = null;

        if (substr($value, 0, 1) == '`') {
            $quote    = '`';
            $quoteEnd = '`';
        } else if (substr($value, 0, 1) == '"') {
            $quote    = '"';
            $quoteEnd = '"';
        } else if (substr($value, 0, 1) == "'") {
            $quote    = "'";
            $quoteEnd = "'";
        } else if (substr($value, 0, 1) == "[") {
            $quote    = '[';
            $quoteEnd = ']';
        }

        if (null !== $quote) {
            $value = str_replace($quoteEnd . '.' . $quote, '.', $value);
            if (substr($value, 0, 1) == $quote) {
                $value = substr($value, 1);
            }
            if (substr($value, -1) == $quoteEnd) {
                $value = substr($value, 0, -1);
            }
        }

        return $value;
    }

    /**
     * Determine if the string is valid SQL (with INSERT VALUES)
     *
     * @param  string $string
     * @return boolean
     */
    public static function isValid($string)
    {
        return (stripos($string, 'INSERT INTO') !== false);
    }

}
