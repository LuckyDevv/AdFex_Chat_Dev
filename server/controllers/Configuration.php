<?php

namespace controllers;
final class Configuration
{
    public const SETTINGS = [
        "name" => "YattoGram",
        "description" => "YattoGram - Это современный мессенджер. Современный дизайн, удобство использования и эргономичность.",
        "logo" => "src/images/logo-2.png",
        "banner" => "",
        "domain" => ""
    ];
    public const MYSQL_CONNECTION = [
        "host" => "localhost", // Хост (айпи адрес) БД
        "port" => "3306", // Порт БД
        "user" => "root", // Пользователь БД
        "password" => "", // Пароль от БД
    ];
    public const MAIL_SETTINGS = [
        "name" => "YattoGram",
        "smtp" => [
            "address" => "smtp.gmail.com",
            "port" => 465,
            "username" => "", // Введите сюда почту
            "password" => "", // Введите сюда пароль/пароль приложения
            "protocol" => "ssl"
        ]
    ];
}
