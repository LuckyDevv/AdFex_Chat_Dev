<?php
require __DIR__.'/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

date_default_timezone_set('Europe/Moscow');

/*$id = (new AutoAuthController($_COOKIE))->handle();
if ($id <= 0) {
    header('Location: auth/');
    unset($_COOKIE['session_id']);
    setcookie('session_id', '', '-1', '/');
    die();
}else{
    setcookie('session_id', $_COOKIE["session_id"], time() + 604800, "/");
}*/

$loader = new FilesystemLoader('server/resources/twig');
$twig = new Environment($loader, [
    'cache' => 'server/resources/twig_cache',
    'autoescape' => false,
]);

echo $twig->render('index.twig');