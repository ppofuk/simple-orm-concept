# Simple read ORM used on collage project
Example: 
```php
<?php
require_once('entity.php');

$user = new Entity('User');
$user->Name = uniqid();
$user->Description = uniqid("Uniqid description ");
$user->insert();

$users = new Entity('User');
$all = $users->getAll();

foreach($all as $obj) {
    echo $obj->id . ': ' . $obj->Name . '<br>';
    $obj->delete();
}
?> 
```