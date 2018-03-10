# Example 1 (with the FileLoader class)

```php
// Load and merge config files (need to return an array)
$loader = new FileLoader(['conf1.php', 'conf2.php']);
$array = $loader->load();
// init the config class with the array data
$config = new Config($array);
// use the dot notation to access the data in the array : it's the same as array['address_book']['admin']['email']
echo($config->get('address_book.admin.email'));
// you can use the syntax like an Array object
echo($config['address_book.admin.email']);
```

# Example 1 (with an Array)

```php
$config = new Config([
    'host' => 'test.github.io',
    'log' => [
        'handler' => 'monolog',
        'path' => 'log/test.log',
    ],
]);

echo($config['host.log.path']);
```
