# Config

$loader = new FileLoader(['conf1.php', 'conf2.php']);
$array = $loader->load();

$config = new Config($array);

echo($config->get('address_book.admin.email'));	
