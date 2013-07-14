# FluentDbal
FluentDbal is an easy to use and framework independant fluent database abstraction layer on top of PDO.
It helps you to structure database queries easly without creating overhead.

## Key features
* Allows reusing prepared statements
* Lightweight
* Promotes use of prepared statements, preventing SQL injection hacks

## Starting with FluentDbal
1. Make sure PHP 5.3.* is installed
2. Install FluentDbal using Composer or manually
3. Create a PDO instance
4. Create instance of FluentDbal
5. Use FluentDbal to create queries (sharing PDO connection)

## Example
```php

$pdo = new \PDO('mysql:host=127.0.0.1;dbname=world', '', '');

$dbal = new FluentDbal($pdo);

$query = $dbal
    ->newQuery()
    ->select('city.Name,country.name, city.District')
    ->from('city')
    ->leftJoin('country', 'country.Code = city.CountryCode')
    ->where('city.CountryCode = ?', 'NLD')        
        
    // Orderby can be repeated
    ->orderby('city.Population','DESC')
    ->orderby('city.Name','ASC')
        
    ->limit(1);
    
$city = $query->execute();

print_r($city->fetch());
// stdClass Object
// (
//    [Name] => Amsterdam
//    [name] => Netherlands
//    [District] => Noord-Holland
// )

```


## License

(MIT License)

Copyright (c) 2013 Elze Kool <info@kooldevelopment.nl>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
