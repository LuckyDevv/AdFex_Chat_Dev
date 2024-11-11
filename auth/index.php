<?php
require __DIR__.'/../vendor/autoload.php';

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

$loader = new FilesystemLoader('../server/resources/twig');
$twig = new Environment($loader, [
    'cache' => '../server/resources/twig_cache',
    'autoescape' => false,
]);

$site_info = \controllers\Configuration::SETTINGS;
echo $twig->render("auth.twig", [
    "site_logo" => $site_info["logo"],
    "site_name" => $site_info["name"],
    "site_description" => $site_info["description"],
    "site_domain" => $site_info["domain"],
    "site_banner" => $site_info["banner"],
]);