<?php
namespace db_managers;

use controllers\Configuration;
use PDO;
use PDOException;

class DB {
    protected \PDO $db;
    public bool $die = false;
    public function __construct()
    {
        @mkdir(__DIR__.'/../logs/');
        try {
            $settings = Configuration::MYSQL_CONNECTION;
            $this->db = new PDO("mysql:host=" . $settings['host'] . ":" . $settings["port"], $settings['user'], $settings['password']);
            $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $this->db->query("CREATE DATABASE IF NOT EXISTS `yattogram_db`;");
            $this->db->query("SET NAMES utf8;");
            $this->db->exec("USE `yattogram_db`;");
        }catch (\PDOException $e) {
            $this->createLog("DB", $e);
            $this->die = true;
        }
    }
    protected function createLog(string $className, PDOException $exception): void
    {
        $text = "[" . date("d.m.Y H:i:s") . "] Произошла ошибка в ".$className.": ".$exception->getMessage().". На строке ".$exception->getLine().", файл: ".$exception->getFile()."\n\n";
        file_put_contents(__DIR__.'/../logs/'.$className.'_Errors.log', $text, FILE_APPEND);
    }
}