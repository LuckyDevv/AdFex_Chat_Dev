<?php

namespace socket;

use db_managers\Accounts_DB;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;

class SocketHandler implements MessageComponentInterface
{
    private const CONNECTION = "x0x01";
    private const MESSAGE = "x0x02";
    private const RESPONSE = "x0x03";
    private const DISCONNECTION = "x0x04";
    private const DEBUG = "x0x05";
    private const ERROR = "x0x06";
    private const JSON_OPTIONS = JSON_UNESCAPED_UNICODE;
    private SplObjectStorage $clients;
    private array $uuid_requests = [];

    public function __construct() {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $conn->user_id = 0;
        $conn->authorized = false;
        $this->clients->attach($conn);
        $this->log("Новое подключение: \e[0;33m".$conn->remoteAddress."\e[0m", self::CONNECTION);
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        $this->log("Клиент отключился: \e[0;33m".$conn->remoteAddress."\e[0m", self::DISCONNECTION);
        unset($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    /*
    RESPONSE CODES:
    1 - Успешная авторизация по сессии
    2 - Аккаунт с введённой почтой найден, нужно перейти к странице ввода пароля
    3 - Аккаунт с введённой почтой не найден, нужно перейти к странице регистрации
    4 - Все данные при регистрации верны, код подтверждения регистрации отправлен на почту
    5 - Успешная регистрация, отправляется Session_ID к клиенту
    6 - Пароль при авторизации верный, код подтверждения авторизации отправлен на почту
    7 - Успешная авторизация, отправляется Session_ID к клиенту

    ERROR CODES:
    1 - Ошибка при чекинге почты
    3 - Ошибки с почтой при регистрации
    4 - Ошибки с именем при регистрации
    5 - Ошибки с фамилией при регистрации
    6 - Ошибки с паролем при регистрации
    7 - Ошибки с подтверждением пароля при регистрации
    8 - Подтверждение пароля не совпадает с паролем
    9 - Ошибки при завершении регистрации / введённым кодом подтверждения
    10 - Ошибки с паролем при авторизации
    11 - Ошибка авторизации / введённым кодом подтверждения
    */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        if (!isset($from->remoteAddress)) {
            $this->log("Поломанный клиент!", self::ERROR);
            return;
        }
        $decode = json_decode($msg, true);
        if (is_array($decode)) {
            $twig = new Environment(new FilesystemLoader(__DIR__.'/../resources/twig'), [
                'cache' => __DIR__.'/../resources/twig_cache',
                'autoescape' => false,
            ]);
            switch ($decode["type"] ?? null) {
                case "session_auth":
                    $this->log("Запрос на авторизацию от \e[0;33m".$from->remoteAddress."\e[0m", self::MESSAGE);
                    break;
                case "email_check":
                    $this->log("Запрос на чек почты от \e[0;33m".$from->remoteAddress."\e[0m", self::MESSAGE);
                    if (isset($decode["email"], $decode["uuid"])) {
                        $accounts_db = new Accounts_DB();
                        $ans = $accounts_db->account_exists_email($decode["email"]);
                        if ($ans === 1) {
                            $this->sendMessage($from, $decode["email"], 2, false, false);
                            $this->log("Данная почта зарегистрирована - \e[0;33m".$from->remoteAddress."\e[0m", self::RESPONSE);
                            $this->uuid_requests[$decode["uuid"]] = [
                                "type" => "auth",
                                "email" => $decode["email"],
                            ];
                        }elseif($ans === 0 || $ans === -1) {
                            $validator = new EmailValidator();
                            $multipleValidations = new MultipleValidationWithAnd([
                                new RFCValidation(),
                                new DNSCheckValidation()
                            ]);
                            if ($validator->isValid($decode["email"], $multipleValidations)) {
                                $this->sendMessage($from, $decode["email"], 3, false, false);
                                $this->log("Данная почта не зарегистрирована - \e[0;33m".$from->remoteAddress."\e[0m", self::RESPONSE);
                            }else $this->sendMessage($from, "Вы ввели неправильную почту", 1, true);
                        }else{
                            $this->sendMessage($from, "Произошла ошибка! Код ошибки: ".$ans.".02", 1, true);
                        }
                    }else $this->sendMessage($from, "Не введён Email!", 1, true);
                    break;
                case "registration_step1":
                    if (isset($decode["email"], $decode["uuid"])) {
                        $accounts_db = new Accounts_DB();
                        if ($accounts_db->account_exists_email($decode["email"]) !== 1) {
                            $validator = new EmailValidator();
                            $multipleValidations = new MultipleValidationWithAnd([
                                new RFCValidation(),
                                new DNSCheckValidation()
                            ]);
                            if ($validator->isValid($decode["email"], $multipleValidations)) {
                                if (isset($decode["name"])) {
                                    if (mb_strlen($decode["name"]) <= 15) {
                                        if (isset($decode["surname"])) {
                                            if (mb_strlen($decode["surname"]) <= 20) {
                                                if (isset($decode["password"])) {
                                                    if (strlen($decode["password"]) >= 8) {
                                                        if (strtolower($decode["password"]) != $decode["password"]) {
                                                            if (isset($decode["password_confirmation"])) {
                                                                if ($decode["password_confirmation"] === $decode["password"]) {
                                                                    // TODO: Send code to email
                                                                    $code = rand(100000, 999999);
                                                                    $this->uuid_requests[$decode["uuid"]] = [
                                                                        "type" => "reg",
                                                                        "email" => $decode["email"],
                                                                        "uuid" => $decode["uuid"],
                                                                        "name" => $decode["name"],
                                                                        "surname" => $decode["surname"],
                                                                        "password" => $decode["password"],
                                                                        "code" => $code
                                                                    ];
                                                                    $this->sendMessage($from, "Код подтверждения отправлен на почту!", 4);
                                                                    $this->log($code, self::DEBUG);
                                                                }else $this->sendMessage($from, "Подтверждение пароля не совпадает с паролем!", 8, true);
                                                            }else $this->sendMessage($from, "Вы не ввели подтверждение пароля!", 7, true);
                                                        }else $this->sendMessage($from, "Пароль должен содержать минимум 1 заглавную букву!", 6, true);
                                                    }else $this->sendMessage($from, "Пароль должен состоять минимум из 8 символов!", 6, true);
                                                }else $this->sendMessage($from, "Вы не ввели пароль!", 6, true);
                                            }else $this->sendMessage($from, "Фамилия не должна быть длиннее 20 символов!", 5, true);
                                        }else $this->sendMessage($from, "Вы не ввели фамилию!", 5, true);
                                    }else $this->sendMessage($from, "Имя не должно быть длиннее 15 символов!", 4, true);
                                }else $this->sendMessage($from, "Вы не ввели имя!", 4, true);
                            }else $this->sendMessage($from, "Такой почты не существует!", 3, true);
                        }else $this->sendMessage($from, "Такая почта уже зарегистрирована!", 3, true);
                    }else $this->sendMessage($from, "Вы не ввели почту!", 3, true);
                    break;
                case "registration_step2":
                    if (isset($decode["uuid"], $decode["code"])) {
                        if (isset($this->uuid_requests[$decode["uuid"]]) && $this->uuid_requests[$decode["uuid"]]["type"] == "reg") {
                            $user_info = $this->uuid_requests[$decode["uuid"]];
                            if ($decode["code"] == $user_info["code"]) {
                                $accounts_db = new Accounts_DB();
                                if ($accounts_db->account_reg(
                                    $user_info["email"],
                                    $user_info["name"],
                                    $user_info["surname"],
                                    $user_info["password"]
                                )) {
                                    // TODO: Create session and send session id to client
                                    $this->sendMessage($from, "Вы успешно зарегистрированы!", 5);
                                }else $this->sendMessage($from, "Не удалось зарегистрировать аккаунт!", 9, true);
                            }else $this->sendMessage($from, "Вы ввели неверный код!", 9, true);
                        }else $this->sendMessage($from, "Запрос на регистрацию не создан!", 9, true);
                    }else $this->sendMessage($from, "Вы не ввели код!", 9, true);
                    break;
                case "auth_password":
                    if (isset($decode["uuid"], $decode["password"])) {
                        if (isset($this->uuid_requests[$decode["uuid"]]) && $this->uuid_requests[$decode["uuid"]]["type"] === "auth") {
                            $password = $decode["password"];
                            $accounts_db = new Accounts_DB();
                            switch (($ans = $accounts_db->account_auth_email($this->uuid_requests[$decode["uuid"]]["email"], $password))) {
                                case 1:
                                    $this->sendMessage($from, "Email code sended", 6, false, false);
                                    $code = rand(100000, 999999);
                                    $this->uuid_requests[$decode["uuid"]]["type"] = "auth_finish";
                                    $this->uuid_requests[$decode["uuid"]]["password"] = $password;
                                    $this->uuid_requests[$decode["uuid"]]["code"] = $code;
                                    $this->log($code, self::DEBUG);
                                    break;
                                case 0:
                                    $this->sendMessage($from, "Вы ввели неверный пароль!", 10, true);
                                    break;
                                default:
                                    $this->sendMessage($from, "Произошла ошибка! Код ошибки: ".$ans.".04", 10, true);
                                    break;
                            }
                        }else $this->sendMessage($from, "Запрос на авторизацию не создан! Попробуйте ещё раз.", 10, true);
                    }else $this->sendMessage($from, "Вы не ввели пароль!", 10, true);
                    break;
                case "auth_finish":
                    if (isset($decode["uuid"], $decode["code"])) {
                        if (isset($this->uuid_requests[$decode["uuid"]]) && $this->uuid_requests[$decode["uuid"]]["type"] === "auth_finish") {
                            $user_info = $this->uuid_requests[$decode["uuid"]];
                            if ($decode["code"] == $user_info["code"]) {
                                // TODO: Create session and send session id to user
                                $this->sendMessage($from, "Успешная авторизация!", 7, false, false);
                            }else $this->sendMessage($from, "Вы ввели неверный код!", 11, true);
                        }else $this->sendMessage($from, "Не создан запрос на авторизацию!", 11, true);
                    }else $this->sendMessage($from, "Вы не ввели код!", 11, true);
                    break;
                case "forgot_start":
                    if (isset($decode["uuid"])) {
                        if (isset($this->uuid_requests[$decode["uuid"]]) && $this->uuid_requests[$decode["uuid"]]["type"] === "auth") {
                            $user_info = $this->uuid_requests[$decode["uuid"]];
                            $code = rand(100000, 999999);
                            // TODO: Send code to email
                            $this->sendMessage($from, "Код подтверждения отправлен на почту!", 8, false, false);
                            $this->log($code, self::DEBUG);
                            $this->uuid_requests[$decode["uuid"]]["type"] = "forgot_start";
                        }else $this->sendMessage($from, "Запрос на авторизацию не создан!", 12, true);
                    }else $this->sendMessage($from, "Не передан UUID!", 12, true);
                    break;
                case "forgot_end":
                    if (isset($decode["uuid"])) {
                        if (isset($this->uuid_requests[$decode["uuid"]]) && $this->uuid_requests[$decode["uuid"]]["type"] === "forgot_start") {
                            if (isset($decode["old_password"])) {
                                $accounts_db = new Accounts_DB();
                                $user_info = $this->uuid_requests[$decode["uuid"]];
                                if ($accounts_db->account_auth_email($user_info["email"], $decode["old_password"])) {
                                    if (isset($decode["new_password"])) {
                                        $new_password = $decode["new_password"];
                                        if (strlen($new_password) >= 8) {
                                            if ($new_password != strtolower($new_password)) {
                                                if (isset($decode["new_password_confirm"])) {
                                                    if ($decode["new_password"] == $decode["new_password_confirm"]) {
                                                        if (isset($decode["code"])) {
                                                            if ($user_info["code"] == $decode["code"]) {
                                                                // TODO: Update password
                                                                // TODO: After updating password create session and send session_id to user
                                                                $this->sendMessage($from, "Вы успешно сменили пароль!", 9, false, false);
                                                            }else $this->sendMessage($from, "Вы ввели неверный код подтверждения!", 18, true);
                                                        }else $this->sendMessage($from, "Вы не ввели код подтверждения!", 18, true);
                                                    }else $this->sendMessage($from, "Подтверждение пароля не совпадает с паролем!", 17, true);
                                                }else $this->sendMessage($from, "Вы не ввели подтверждение пароля", 16, true);
                                            }else $this->sendMessage($from, "В пароле должна быть минимум 1 заглавная буква!", 15, true);
                                        }else $this->sendMessage($from, "Пароль должен состоять минимум из 8 символов!", 15, true);
                                    }else $this->sendMessage($from, "Вы не ввели новый пароль!", 15, true);
                                }else $this->sendMessage($from, "Неверный старый пароль!", 14, true);
                            }else $this->sendMessage($from, "Вы не ввели старый пароль!", 14, true);
                        }else $this->sendMessage($from, "Запрос на сброс пароля не создан!", 13, true);
                    }else $this->sendMessage($from, "Не передан UUID!", 13, true);
                    break;
                case "ping":
                    $this->sendMessage($from, "", 1190, false, false);
                    break;
                default:
                    $this->log("Неизвестный запрос от \e[0;33m".$from->remoteAddress."\e[0m", self::MESSAGE);
                    $this->sendMessage($from, "Undefined operation type", 4, true);
                    break;
            }
        }else{
            $this->log("Сообщение от \e[0;33m".$from->remoteAddress."\e[0m: \e[1;34m«".$msg."»\e[0m", self::MESSAGE);
            $this->sendMessage($from, "Undefined message", 25);
        }
    }

    private function log(string $msg, string $type): void
    {
        $prefix = "[Undefined]";
        switch ($type) {
            case self::CONNECTION:
                $prefix = "\e[1;32m[Connection]\e[0m";
                break;
            case self::MESSAGE:
                $prefix = "\e[1;36m[Message]\e[0m";
                break;
            case self::RESPONSE:
                $prefix = "\e[1;33m[Response]\e[0m";
                break;
            case self::DISCONNECTION:
                $prefix = "\e[1;31m[Disconnection]\e[0m";
                break;
            case self::DEBUG:
                $prefix = "\e[1;35m[DEBUG]\e[0m";
                break;
            case self::ERROR:
                $prefix = "\e[1;31m[ERROR]\e[0m";
                break;
        }
        $message = "\e[1;34m[".date("d.m.Y H:i:s")."]\e[0m ".$prefix." - ".$msg."\n";
        print($message);
        file_put_contents(__DIR__."/Socket.log", preg_replace("/\033\[[^m]*m/", '', $message), FILE_APPEND);
    }
    private function findClientsByUserId(int $user_id): array
    {
        $clients = [];
        foreach ($this->clients as $client) {
            if (!isset($client->user_id)) continue;
            if ($client->user_id === $user_id) {
                $clients[] = $client;
            }
        }
        return $clients;
    }
    private function sendMessageByUserId(int $user_id, string $message, int $code=0, bool $isError=false, bool $log=true): void
    {
        $clients = $this->findClientsByUserId($user_id);
        if (count($clients) > 0) {
            foreach ($clients as $client) {
                $this->sendMessage($client, $message, $code, $isError, $log);
            }
        }
    }
    private function sendMessage(ConnectionInterface $connection, string $message, int $code=0, bool $isError=false, bool $log=true): void
    {
        if ($code == 3 || $code == 4) {
            if (isset($connection->receiptsStat)) $connection->receiptsStat = "update";
            if (isset($connection->expensesStat)) $connection->expensesStat = "update";
        }
        if ($log) {
            $this->log("Отправлено сообщение: \e[1;34m«".$message."»\e[0m клиенту \e[0;33m".$connection->remoteAddress."\e[0m", self::RESPONSE);
        }
        $type = $isError ? "error" : "response";
        $message = [
            $type => [
                "message" => $message,
                "code" => $code,
                "timestamp" => time()
            ]
        ];
        $connection->send(json_encode($message, self::JSON_OPTIONS));
    }
}