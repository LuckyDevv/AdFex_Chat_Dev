<?php

namespace controllers;
class Configuration
{
    public const SETTINGS = [
        "name" => "YattoGram",
        "description" => "YattoGram - Это современный мессенджер. Современный дизайн, удобство использования и эргономичность.",
        "logo" => "../src/images/logo-2.png",
        "banner" => "",
        "domain" => ""
    ];
    public const MYSQL_CONNECTION = [
        "host" => "localhost",
        "port" => "3306",
        "user" => "root",
        "password" => "eather1192@How91",
    ];
    public const DATA_SERVER = [
        [
            "name" => "DC1",
            "host" => "1.1.1.1",
            "port" => "3306",
            "user" => "root",
            "password" => "",
        ],
    ];
}