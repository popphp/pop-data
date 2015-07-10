<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Data;

/**
 * Data class
 *
 * @category   Pop
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Data
{

    /**
     * Data file type
     * @var string
     */
    protected $type = null;

    /**
     * Data in PHP
     * @var mixed
     */
    protected $data = null;

    /**
     * Data as string
     * @var string
     */
    protected $string = null;

    /**
     * Constructor
     *
     * Instantiate the data object.
     *
     * @param  mixed $data
     * @throws Exception
     * @return Data
     */
    public function __construct($data)
    {
        // If data is a file
        if ((is_string($data)) &&
            ((stripos($data, '.csv') !== false) ||
             (stripos($data, '.json') !== false) ||
             (stripos($data, '.sql') !== false) ||
             (stripos($data, '.xml') !== false) ||
             (stripos($data, '.yml') !== false) ||
             (stripos($data, '.yaml') !== false)) && file_exists($data)) {
            $this->setString(file_get_contents($data));
            $this->autoDetect();
        // Else, if it's just data
        } else if (!is_string($data)) {
            $this->setData($data);
        // Else if it's a string or stream of data
        } else {
            $this->setString($data);
        }
    }

    /**
     * Get the data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the string
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Get the type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the data
     *
     * @param  mixed $data
     * @return Data
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set the string
     *
     * @param  string $string
     * @return Data
     */
    public function setString($string)
    {
        $this->string = $string;
        return $this;
    }

    /**
     * Serialize the data to a string
     *
     * @param  string $to
     * @param  array  $options
     * @throws Exception
     * @return Data
     */
    public function serialize($to, array $options = [])
    {
        $this->type = strtolower($to);
        $types      = ['csv', 'json', 'sql', 'xml', 'yml', 'yaml'];

        if (!in_array($this->type, $types)) {
            throw new Exception('That data type is not supported.');
        }

        if ($this->type == 'yml') {
            $this->type = 'yaml';
        }

        $class        = 'Pop\\Data\\Type\\' . ucfirst($this->type);
        $this->string = $class::serialize($this->data, $options);
        return $this;
    }

    /**
     * Unserialize the string to data
     *
     * @param  array  $options
     * @throws Exception
     * @return Data
     */
    public function unserialize(array $options = [])
    {
        $class      = 'Pop\\Data\\Type\\' . ucfirst($this->type);
        $this->data = $class::unserialize($this->string, $options);
        return $this;
    }

    /**
     * Output the data file directly.
     *
     * @param  string  $filename
     * @param  boolean $forceDownload
     * @throws Exception
     * @return string
     */
    public function outputToHttp($filename = null, $forceDownload = true)
    {
        if ((null === $this->type) || (null === $this->string)) {
            throw new Exception('Error: The data has not been properly serialized.');
        }

        $mimeTypes = [
            'csv'    => 'text/csv',
            'json'   => 'application/json',
            'sql'    => 'text/plain',
            'xml'    => 'application/xml',
            'yaml'   => 'text/plain'
        ];

        if (null === $filename) {
            $filename = 'pop-data.' . $this->type;
        }

        $headers = [
            'Content-type'        => $mimeTypes[$this->type],
            'Content-disposition' => (($forceDownload) ? 'attachment; ' : null) . 'filename=' . $filename
        ];

        if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
            $headers['Expires']       = 0;
            $headers['Cache-Control'] = 'private, must-revalidate';
            $headers['Pragma']        = 'cache';
        }

        // Send the headers and output the file
        if (!headers_sent()) {
            header('HTTP/1.1 200 OK');
            foreach ($headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }

        echo $this->string;
    }

    /**
     * Save the data file to disk.
     *
     * @param  string $to
     * @throws Exception
     * @return void
     */
    public function writeToFile($to)
    {
        if (null === $this->string) {
            throw new Exception('Error: The data has not been properly serialized.');
        }
        file_put_contents($to, $this->string);
    }

    /**
     * Auto-detect the data type from the string
     *
     * @return Data
     */
    protected function autoDetect()
    {
        // Attempt to auto-detect data type from the string
        if (Type\Json::isValid($this->string)) {
            $this->type = 'json';
        } else if (Type\Xml::isValid($this->string)) {
            $this->type = 'xml';
        } else if (Type\Sql::isValid($this->string)) {
            $this->type = 'sql';
        } else if (Type\Yaml::isValid($this->string)) {
            $this->type = 'yaml';
        } else if (Type\Csv::isValid($this->string)) {
            $this->type = 'csv';
        }

        return $this;
    }

}
