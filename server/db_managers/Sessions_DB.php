<?php

namespace db_managers;

use PDO;
use PDOException;

class Sessions_DB extends DB
{
    public function __construct() {
        try {
            parent::__construct();
            $this->db->exec("
            CREATE TABLE IF NOT EXISTS `sessions` (
                `session_id` VARCHAR(255) NOT NULL,
                `account_email` VARCHAR(255) NOT NULL,
                `password_hash` TEXT NOT NULL,
                `create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expire_time` TIMESTAMP NOT NULL,
                `login_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `browser` TEXT NOT NULL,
                `os` TEXT NOT NULL,
                `uuid` TEXT NOT NULL,
                PRIMARY KEY (`session_id`)
            );");
        }catch(PDOException $e){
            $this->createLog("Sessions_DB", $e);
            $this->die = true;
        }
    }
    public function create_session(string $account_email, string $password_hash, string $browser, string $os, string $uuid, bool $encrypt=true): string {
        try {
            $session_id = hash('sha512', $account_email.$browser.$os.$uuid.time());
            $password_hash = $encrypt ? hash('sha3-512', $password_hash) : $password_hash;
            $prepare = $this->db->prepare("INSERT IGNORE INTO `sessions` VALUES (:session_id, :account_email, :password_hash, DEFAULT, TIMESTAMPADD(DAY, 7, CURRENT_TIMESTAMP), DEFAULT, :browser, :os, :uuid);");
            $prepare->bindValue(':session_id', $session_id);
            $prepare->bindValue(':account_email', $account_email);
            $prepare->bindValue(':password_hash', $password_hash);
            $prepare->bindValue(':browser', $browser);
            $prepare->bindValue(':os', $os);
            $prepare->bindValue(':uuid', $uuid);
            return $prepare->execute() ? $session_id : 'err_1';
        }catch(PDOException $e){
            $this->createLog("Sessions_DB", $e);
            return 'err_2';
        }
    }
    public function remove_session(string $session_id): int {
        try {
            $prepare = $this->db->prepare("DELETE FROM `sessions` WHERE `session_id` = :session_id;");
            $prepare->bindValue(':session_id', $session_id);
            return (int) $prepare->execute();
        }catch(PDOException $e){
            $this->createLog("Sessions_DB", $e);
            return -1;
        }
    }
    public function removeOutdatedSessions(): int {
        try {
            return $this->db->prepare("DELETE FROM `sessions` WHERE unix_timestamp(`expire_time`) < unix_timestamp(CURRENT_TIMESTAMP);")->execute();
        }catch(PDOException $e) {
            $this->createLog("Sessions_DB", $e);
            return -1;
        }
    }
    public function validateSession(string $session_id, string $uuid): int|string {
        try {
            $prepare = $this->db->prepare("SELECT `account_email`,`password_hash`,unix_timestamp(`expire_time`) AS `expire_time`,`uuid` FROM `sessions` WHERE `session_id` = :session_id;");
            $prepare->bindValue(':session_id', $session_id);
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if ($fetch !== false && isset($fetch['password_hash'])) {
                    if ($uuid === $fetch['uuid']) {
                        if ($fetch['expire_time'] > time()) {
                            $response_auth = (new Accounts_DB())->account_auth_email($fetch['account_email'], $fetch['password_hash'], false);
                            return $response_auth === 1 ? base64_decode($fetch['account_email']) : $response_auth;
                        }else return -time();
                    }else return -4;
                }else return -5;
            }else return -6;
        }catch(PDOException $e) {
            $this->createLog("Sessions_DB", $e);
            return -7;
        }
    }
    public function existsSession(string $session_id): bool {
        try {
            $prepare = $this->db->prepare("SELECT `session_id` FROM `sessions` WHERE `session_id` = :session_id;");
            $prepare->bindValue(':session_id', $session_id);
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if ($fetch !== false && isset($fetch['session_id'])) {
                    return true;
                }
            }
        }catch(PDOException $e) {
            $this->createLog("Sessions_DB", $e);
        }
        return false;
    }
}