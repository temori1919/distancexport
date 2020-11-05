## distancexport

![https://img.shields.io/badge/license-MIT-green.svg](https://img.shields.io/badge/license-MIT-green.svg)![Packagist PHP Version Support (custom server)](https://img.shields.io/packagist/php-v/temori/distancexport) ![Packagist Version](https://img.shields.io/packagist/v/temori/distancexport) ![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/temori1919/distancexport)

Data migration tool between databases.

Data migration is possible regardless of RDB.



![](https://user-images.githubusercontent.com/17793990/97781529-703a9600-1bcf-11eb-8e5d-cf7db08c9da1.png)



## Table of Contents

- [Features](#Features)
- [Installation](#Installation)

* [Requirements](#Requirements)
* [Usage](#Usage)
* [Supported RDB driver types](＃Supported RDB driver types)
* [Add RDB driver](#Add RDB driver) 
* [Note](#Note)
* [License](#License)



## Features

- Database data migration tools.

- Data migration between two databases is possible.

- Migrate the data in the source column in the same row as the destination column in a web like Google Spreadsheet.

- The default only supports Mysql and pgsql drivers.  

  If you want to use another driver, you can add a driver classes.



## Requirements

- php 5.4 or later



## Installation

Using composer:

```sh
cd path/to/your/project
composer require temori/distancexport --dev
```



## Usage

- Create a php files.

  If you use any FW, Create a routable controllers.

- Define the following constants.
  - For the destination DB.
    - `DX_DESTINATION_DB_DRIVER` destination DB drivers.
    - `DX_DESTINATION_DB_HOST` destination DB hosts.
    - `DX_DESTINATION_DB_PORT` destination DB ports.
    - `DX_DESTINATION_DB_USERNAME` destination DB accounts.
    - `DX_DESTINATION_DB_PASSWORD` destination DB passwords.
  - For the source DB.
    - `DX_SOURCE_DB_DRIVER` source DB drivers.
    - `DX_SOURCE_DB_HOST` source DB hosts.
    - `DX_SOURCE_DB_PORT` source DB ports.
    - `DX_SOURCE_DB_USERNAME` source DB accounts.
    - `DX_SOURCE_DB_PASSWORD` source DB passwords.
  - When need csrf tokens.
    - `DX_CSRF_TOKEN_NAME` CSRF token name fields.
    - `DX_CSRF_TOKEN` CSRF token fields.

- Create an instance of `\Temori\Distancexport\Distancexport` with the created php file or controller and execute the `init()` method,

  like below.

  ```php
  $dis = new \Temori\Distancexport\Distancexport();
  $dis->init();
  ```

- Open the above php file or controller url.

- Since table names, data types, key types, etc. are lined up like a spreadsheet, copy and paste the column name you want to migrate next to the migration destination database column.

  

  The columns required for migration are `Field` in` Destination Databases` and `Field` in` Source Databases`.

  

  Data migration is ignored if `Field` in` Source Databases` is blank.

  

  If you enter a character string in the `Uniformity` column as an option, the character string described in `Uniformity` will be added to all records.

- The `Dry run` button performs test execution, and the` Run` button executes data migration.

  

  If an exception occurs, an error message will be displayed in `Results` and` message`.

  

## Supported RDB driver types

- Mysql
- postgresql



## Add RDB driver

If you want you can add other driver classes.



For the class to be created, implement the `BaseDriver` class and inherit the `Connect` class.



In addition, create a method that produces the same execution result as the following class.

> [DataBases/Drivers/Mysql.php](https://github.com/temori1919/distancexport/blob/master/src/DataBases/Drivers/Mysql.php)



Specify the driver class added when creating the instance as shown below.

```php
$dis = new \Temori\Distancexport\Distancexport(\Some\NameSpace\DestinationDriverClass::class, \Some\NameSpace\SourceDriverClass::class);
```



## Note

**[warning]**

Since the entire database structure is displayed, please be careful about security when using it.



License
-------

Paddington is licensed under the [MIT](https://opensource.org/licenses/mit-license.php) license.  
Copyright &copy; 2020, Atushi Inoue