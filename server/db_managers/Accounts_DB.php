<?php

namespace db_managers;

use PDO;
use PDOException;
use PDOStatement;

class Accounts_DB extends DB
{
    public function __construct() {
        try {
            parent::__construct();
            $this->db->exec("
            CREATE TABLE IF NOT EXISTS accounts (
                `id` BIGINT NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `name` TEXT NOT NULL,
                `surname` TEXT NOT NULL,
                `username` VARCHAR(30) NOT NULL,
                `premium` BOOLEAN NOT NULL DEFAULT FALSE,
                `password_hash` TEXT NOT NULL,
                `type` INTEGER NOT NULL DEFAULT 1,
                `last_online` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `hide_online` BOOLEAN NOT NULL DEFAULT FALSE,
                `avatar` TEXT NOT NULL,
                `is_admin` BOOLEAN NOT NULL DEFAULT FALSE,
                `verified` BOOLEAN NOT NULL DEFAULT FALSE,
                `user_info` TEXT NOT NULL,
                `phone_number` TEXT NOT NULL,
                `email_auth` BOOLEAN NOT NULL DEFAULT TRUE,
                `user_chats` TEXT NOT NULL,
                PRIMARY KEY (`id`, `email`, `username`)
            );");
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            $this->die = true;
        }
    }
    public function account_exists_email(string $email): int {
        try {
            $prepare = $this->db->prepare("SELECT `email` FROM `accounts` WHERE `email` = :email");
            $email = base64_encode($email);
            $prepare->bindValue(":email", $email);
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if ($fetch !== false) {
                    return true;
                }else return -1;
            }else return -2;
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            return -3;
        }
    }
    public function account_exists_id(int $id): int {
        try {
            $prepare = $this->db->prepare("SELECT `email` FROM `accounts` WHERE `id` = :id");
            $prepare->bindValue(":id", $id);
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if ($fetch !== false) {
                    return true;
                }else return -1;
            }else return -2;
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            return -3;
        }
    }
    public function account_reg(string $email, string $name, string $surname, string $password, int $account_type=1, bool $encrypt=true): int {
        try {
            $id = $this->getMaxId();
            switch ($id) {
                case -4:
                case -3:
                case -2:
                    return -2;
                case -1:
                    $id = 1;
                    break;
                default:
                    $id++;
            }
            $username = "id_user".$id;
            $password = $encrypt ? hash("sha3-512", $password) : $password;
            $prepare = $this->db->prepare("INSERT INTO `accounts` VALUES (:id,:email,:name,:surname,:username,DEFAULT,:password,:type,DEFAULT,DEFAULT,'',DEFAULT,DEFAULT,'','',DEFAULT,:chats);");
            $prepare->bindValue(":id", $id, PDO::PARAM_INT);
            $email = base64_encode($email);
            $prepare->bindValue(":email", $email);
            $name = base64_encode($name);
            $prepare->bindValue(":name", $name);
            $surname = base64_encode($surname);
            $prepare->bindValue(":surname", $surname);
            $prepare->bindValue(":username", $username);
            $prepare->bindValue(":password", $password);
            $prepare->bindValue(":type", $account_type, PDO::PARAM_INT);
            $prepare->bindValue(":chats", '');
            return $prepare->execute() ? 1 : 0;
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            return -1;
        }
    }
    public function account_auth_email(string $email, string $password, bool $encrypt=true): int {
        $prepare = $this->db->prepare("SELECT `password_hash` FROM `accounts` WHERE `email` = :email;");
        $email = base64_encode($email);
        $prepare->bindValue(":email", $email);
        return $this->auth_account($prepare, $password, $encrypt);
    }
    public function account_auth_id(int $id, string $password, bool $encrypt=true): int {
        $prepare = $this->db->prepare("SELECT `password_hash` FROM `accounts` WHERE `id` = :id;");
        $prepare->bindValue(":id", $id, PDO::PARAM_INT);
        return $this->auth_account($prepare, $password, $encrypt);
    }

    public function account_password_update_email(string $email, string $new_password, bool $encrypt=true): int {
        try {
            $email = base64_encode($email);
            $new_password = $encrypt ? hash("sha3-512", $new_password) : $new_password;
            $prepare = $this->db->prepare("UPDATE `accounts` SET `password_hash` = :new_password WHERE `email` = :email;");
            $prepare->bindValue(":new_password", $new_password);
            $prepare->bindValue(":email", $email);
            return $prepare->execute() ? 1 : 0;
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            return -1;
        }
    }
    public function name_get_by_email(string $email): ?string {
        try {
            $email = base64_encode($email);
            $prepare = $this->db->prepare("SELECT `name` FROM `accounts` WHERE `email`=:email;");
            $prepare->bindValue(":email", $email);
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if ($fetch !== false && isset($fetch["name"])) {
                    return base64_decode($fetch["name"]);
                }
            }
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
        }
        return null;
    }
    public function get_chats(string $email): array {
        $result = [];
        try {
            $prepare = $this->db->prepare("SELECT `user_chats` FROM `accounts` WHERE `email` = :email;");
            $prepare->bindValue(":email", $email);
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if ($fetch !== false) {
                    $result = explode(',', $fetch["user_chats"] ?? '');
                }
            }
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
        }
        return $result;
    }
    public function add_chat(string $email, int $chat_id): int {
        try {
            $chats = $this->get_chats($email);
            if (count($chats) > 0 && !in_array($chat_id, $chats)) {
                $chats[] = $chat_id;
                $chats = implode(',', $chats);
                $prepare = $this->db->prepare("UPDATE `accounts` SET `user_chats` = :chats WHERE `email` = :email;");
                $prepare->bindValue(":chats", $chats);
                $prepare->bindValue(":email", $email);
                return $prepare->execute() ? 1 : 0;
            }else return -1;
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            return -2;
        }
    }
    public function remove_chat(string $email, int $chat_id): int
    {
        try {
            $chats = $this->get_chats($email);
            if (count($chats) > 0 && in_array($chat_id, $chats)) {
                $index = array_search($chat_id, $chats);
                if ($index !== false) {
                    unset($chats[$index]);
                    $chats = implode(',', $chats);
                    $prepare = $this->db->prepare("UPDATE `accounts` SET `user_chats` = :chats WHERE `email` = :email;");
                    $prepare->bindValue(":chats", $chats);
                    $prepare->bindValue(":email", $email);
                    return $prepare->execute() ? 1 : 0;
                }else return -1;
            }else return -2;
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            return -3;
        }
    }

    private function getMaxId(): int {
        try {
            $stmt = $this->db->query("SELECT MAX(`id`) AS 'max_id' FROM `accounts`;");
            if ($stmt instanceof PDOStatement) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($result)) return $result['max_id'] ?? -1;
                else return -2;
            }else return -3;
        }catch(PDOException $e) {
            $this->createLog("AccountsDB", $e);
            return -4;
        }
    }
    private function auth_account(PDOStatement $prepare, string $password, bool $encrypt): int {
        try {
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if (isset($fetch['password_hash'])) {
                    $password = $encrypt ? hash("sha3-512", $password) : $password;
                    if ($password == $fetch['password_hash']) {
                        return 1;
                    }else return 0;
                }else return -1;
            }else return -2;
        }catch(PDOException $e) {
            $this->createLog("Accounts_DB", $e);
            return -3;
        }
    }
}