<?php

namespace controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailController
{
    private PHPMailer $mail;
    private string $email_to;
    private string $name_to;
    private array $smtp_settings = Configuration::MAIL_SETTINGS;
    public function __construct(string $email_to, string $name_to)
    {
        $mail = new PHPMailer(true);
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPAuth = true; // Set if SMTP Auth required
        $mail->SMTPSecure = $this->smtp_settings["smtp"]["protocol"]; // Use SSL Connection
        $mail->Port = $this->smtp_settings["smtp"]["port"]; // SSL Gmail SMTP Port
        $mail->Host = $this->smtp_settings["smtp"]["address"]; // SMTP Gmail address
        $mail->Username = $this->smtp_settings["smtp"]["username"]; // Login
        $mail->Password = $this->smtp_settings["smtp"]["password"]; // Password
        $mail->CharSet = "UTF-8";
        $this->mail = $mail;
        $this->email_to = $email_to;
        $this->name_to = $name_to;
    }
    public function sendCode(string $code, int $type): bool
    {
        $message = "";
        $title = "";
        switch ($type) {
            case 0:
                $title = "Подтверждение регистрации";
                $message = "Здравствуйте, ".$this->name_to."! Нам пришёл запрос на регистрацию аккаунта на ".$this->smtp_settings["name"]." от вашего имени. Если это были вы, введите данный код на нашем сайте: ".$code.". Если это были не вы, то просто проигнорируйте это письмо.";
                break;
            case 1:
                $title = "Подтверждение входа";
                $message = "Здравствуйте, ".$this->name_to."! Для подтверждения входа в аккаунт, введите код: ".$code.". Если это были не вы, срочно смените пароль.";
                break;
            case 2:
                $title = "Восстановление пароля";
                $message = "Здравствуйте, ".$this->name_to."! Для подтверждения смены пароля, введите код: ".$code.". Если это были не вы, просто проигнорируйте данное сообщение.";
                break;
        }
        if ($message == "" || $title == "") return false;
        try{
            $this->mail->addAddress($this->email_to, $this->name_to); // Add recipient
            $this->mail->setFrom($this->smtp_settings["smtp"]["username"], $this->smtp_settings["name"]); // Add yourself
            $this->mail->Subject = $title; // Mail Title
            $this->mail->Body = $message; // Mail Content
            $this->mail->send(); // Send Mail
            return true;
        } catch(Exception $e){ // If mail not sent
            echo "Fail - " . $this->mail->ErrorInfo; // Log error
            return false;
        }
    }
}