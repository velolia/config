# Velolia Container
Velolia Config is a lightweight and efficient config management.

# Basic Usage

create dir config on a root directory config/app.php

```php
return [
    'name' => 'Velolia Config',
    'version' => '1.0.0',
];

```

```php

$config = Config::get('app.name');
$config = Config::set('app.name', 'New App Name');

OR

$config = config('app.name');
$config = config('app.name', 'New App Name');

```php