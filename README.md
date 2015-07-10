Pop Data Component
==================

[![Build Status](https://travis-ci.org/popphp/pop-data.svg?branch=master)](https://travis-ci.org/popphp/pop-data)

OVERVIEW
--------
Pop Data is a component of the Pop PHP Framework 2. It provides a streamlined way to convert common data types.
With it you can easily give it some native PHP data and quickly produce a serialized version of that data in a
common data type, such as CSV, JSON, SQL, XML or YAML. Or, conversely, you can give it some serializes data, an
it will detect the format and convert it to native PHP data.

INSTALL
-------

Install `Pop Data` using Composer.

    composer require popphp/pop-data

BASIC USE
---------

### Serialize Data

```php
$phpData = [
    [
        'first_name' => 'Bob',
        'last_name'  => 'Smith'
    ],
    [
        'first_name' => 'Jane',
        'last_name'  => 'Smith'
    ]
];

$data = new Pop\Data\Data($phpData);

$csvString   = $data->serialize('csv');
$jsonString  = $data->serialize('json');
$sqlString   = $data->serialize('sql');
$xmlString   = $data->serialize('xml');
$yamlString  = $data->serialize('yaml');
```

The $csvString variable now contains:

    first_name,last_name
    Bob,Smith
    Jane,Smith

The $jsonString variable now contains:

    [
        {
            "first_name": "Bob",
            "last_name": "Smith"
        },
        {
            "first_name": "Jane",
            "last_name": "Smith"
        }
    ]

The $sqlString variable now contains:

    INSERT INTO data (first_name, last_name) VALUES
    ('Bob', 'Smith'),
    ('Jane', 'Smith');


The $xmlString variable now contains:

    <?xml version="1.0" encoding="utf-8"?>
    <data>
      <row>
        <first_name>Bob</first_name>
        <last_name>Smith</last_name>
      </row>
      <row>
        <first_name>Jane</first_name>
        <last_name>Smith</last_name>
      </row>
    </data>

The $yamlString variable now contains:

    ---
    - first_name: Bob
      last_name: Smith
    - first_name: Jane
      last_name: Smith
    ...

### Unserialize Data

You can either pass the object a direct string of serialized data or a file containing a string of serialized data. 

##### CSV

```php
$csv     = new Pop\Data\Data($csvString);
$phpData = $csv->unserialize();
```

##### XML

```php
$xml     = new Pop\Data\Data('/path/to/file.xml');
$phpData = $xml->unserialize();
```

### Convert Between Data Types

```php
$csv = new Pop\Data\Data($csvString);
$xml = $csv->convert('xml');
```

### Write Data to File

```php
$phpData = [ ... ];

$data = new Pop\Data\Data($phpData);
$data->serialize('csv');
$data->writeToFile('/path/to/file.csv');
```

### Output to HTTP

```php
$phpData = [ ... ];

$data = new Pop\Data\Data($phpData);
$data->serialize('csv');
$data->outputToHttp();
```

##### Force download of file

```php
$phpData = [ ... ];

$data = new Pop\Data\Data($phpData);
$data->serialize('csv');
$data->outputToHttp('my-file.csv', true);
```
