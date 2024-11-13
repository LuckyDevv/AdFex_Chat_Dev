<?php
require __DIR__.'/vendor/autoload.php';

use controllers\AutoAuthController;
use controllers\Configuration;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

date_default_timezone_set('Europe/Moscow');

$loader = new FilesystemLoader('server/resources/twig');
$twig = new Environment($loader, [
    'cache' => false,
    'autoescape' => false,
]);

if (isset($_COOKIE["session_id"])) {
    $id = (new AutoAuthController($_COOKIE))->handle();
    if (!is_string($id)) {
        unset($_COOKIE['session_id']);
        setcookie('session_id', '', '-1', '/');
        renderAuth($twig);
    }else{
        setcookie('session_id', $_COOKIE["session_id"], time() + 604800, "/");
        echo $twig->render("index.twig");
    }
}else renderAuth($twig);

function renderAuth(Environment $twig): void
{
    $site_info = Configuration::SETTINGS;
    echo $twig->render("auth.twig", [
        "site_logo" => $site_info["logo"],
        "site_name" => $site_info["name"],
        "site_description" => $site_info["description"],
        "site_domain" => $site_info["domain"],
        "site_banner" => $site_info["banner"],
    ]);
}