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
        "host" => "localhost",
        "port" => "3306",
        "user" => "root",
        "password" => "eather1192@How91",
    ];
    public const MAIL_SETTINGS = [
        "name" => "YattoGram",
        "smtp" => [
            "address" => "smtp.gmail.com",
            "port" => 465,
            "username" => "wallet.eather@gmail.com",
            "password" => "bcnd bkbx mkkj lhmu",
            "protocol" => "ssl"
        ]
    ];
}