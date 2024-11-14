<?php

namespace db_managers;

use PDO;
use PDOException;

class Chats_DB extends DB
{
    /*
     * chats:
- chat_id | BIGINT (Идентификатор чата)
- type | INT (0 - ЛС, 1 - беседа, 2 - канал, 3 - бот. для будущего апгрейда)
- members | TEXT (список участников)
- chat_name | TEXT (имя чата. для ЛС имя контакта)
- chat_avatar | TEXT (путь к аватарке чата. для ЛС - аватар контакта)
- admins | TEXT (список администраторов через ",". для ЛС не работает. для будущего апгрейда)
- premium | BOOLEAN (есть ли у группы/беседы премиум статус. не работает для ЛС. для будущего апгрейда)
- last_message_time | TIMESTAMP (время последнего сообщения, для сортировки)
- allow_forward | BOOLEAN (запретить/разрешить пересылку сообщений, для будущего апгрейда)
- access | BOOLEAN (0 приватный чат, 1 публичный чат. для ЛС всегда 0, для будущего апгрейда)
- backgroup_image | TEXT (изображение фона в чате, для будущего апгрейда)
- messages_config | TEXT (путь к конфигу с сообщениями в чате)
     */
    public function __construct()
    {
        try {
            parent::__construct();
            $this->db->exec("
            CREATE TABLE IF NOT EXISTS `chats` (
                `chat_id` BIGINT NOT NULL,
                `type` INT NOT NULL,
                `members` TEXT NOT NULL,
                `chat_name` TEXT NOT NULL,
                `chat_avatar` TEXT NOT NULL,
                `admins` TEXT NOT NULL,
                `premium` BOOLEAN NOT NULL,
                `verified` BOOLEAN NOT NULL DEFAULT FALSE,
                `allow_forward` BOOLEAN NOT NULL DEFAULT TRUE,
                `last_message` TEXT,
                `last_message_time` TIMESTAMP,
                `access` BOOLEAN NOT NULL DEFAULT FALSE,
                `background_image` TEXT NOT NULL,
                `messages_config` TEXT NOT NULL,
                PRIMARY KEY (`chat_id`)
            );");
        }catch(\PDOException $e) {
            $this->createLog("Chats_DB", $e);
            $this->die = true;
        }
    }
    public function get_chat_info(int $chat_id): array {
        $result = [];
        try {
            $prepare = $this->db->prepare("SELECT * FROM `chats` WHERE `chat_id` = :chat_id");
            $prepare->bindValue(":chat_id", $chat_id, PDO::PARAM_INT);
            if ($prepare->execute()) {
                $fetch = $prepare->fetch(PDO::FETCH_ASSOC);
                if ($fetch !== false) {
                    if (isset($fetch['chat_id'])) {
                        $result = $fetch;
                    }
                }
            }
        }catch(PDOException $e) {
            $this->createLog("Chats_DB", $e);
        }
        return $result;
    }
}