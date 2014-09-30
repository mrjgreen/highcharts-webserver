##A Simple Config Loader for PHP

Merge a set of php config arrays from files in nested folders (using `array_replace_recursive`) based on a single enironment setting matching the folder structure you want to load.

### Installation:

via composer - add the package to the require section in your composer.json file:

    "require" : {    
        "joegreen0991/config"   : "dev-master"
    }

### Example:

~~~
app
|
|__config
|  |
|  |____ production
|  |        |
|  |        |_______ server1
|  |        |       |___ redis.php
|  |        |       |___ database.php
|  |        |
|  |        |_______ server2
|  |        |       |___ database.php
|  |        |
|  |        |_______ database.php
|  |
|  |____ app.php
|  |____ database.php
|  |____ redis.php

~~~

~~~PHP
<?php
// in database.php

return array(
    'config_value' => 'foo',
    'config_value2' => 'bar'
);

~~~

~~~PHP
<?php
// in production/database.php

return array(
    'config_value' => 'baz',
);

~~~

~~~PHP
<?php
// in production/server1/database.php

return array(
    'new_config_only_for_server1' => 'boo',
);

~~~

~~~PHP

$environment = '';

$config = new Config\Repository(new Config\FileLoader(__DIR__ . '/config'), $environment);

var_dump($config['database']);
/*
array(
   'config_value' => 'foo',
   'config_value2' => 'bar'
);
*/

//________________________________________________________________________

$environment = 'production.server1';

$config = new Config\Repository(new Config\FileLoader(__DIR__ . '/config'), $environment);

var_dump($config['database']);
/*
array(
   'config_value' => 'baz',
   'config_value2' => 'bar',
   'new_config_only_for_server1' => 'boo',
);
*/

~~~


### Dot notation

You can nest arrays in your config file and access them via the dot notation:

~~~PHP
<?php
// in database.php

return array(
    'connections' => array(
        'local' => array(
            'host' => 'localhost'
        ),
        'shared' => array(
            'host' => '10.10.10.1'
        ),
        'external' => array(
            'host' => '156.12.102.1'
        )
    )
);


var_dump($config['database.connection.local.host']);

/*
string(9) "localhost"
*/
~~~
