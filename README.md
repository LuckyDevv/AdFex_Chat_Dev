# 💬 YattoGram (AdFex Chat) — веб-мессенджер на PHP и WebSocket

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Ratchet](https://img.shields.io/badge/Ratchet-WebSockets-4B32C3?style=flat-square)](https://socketo.me)
[![Twig](https://img.shields.io/badge/Twig-3.0-green?style=flat-square)](https://twig.symfony.com)

## 📌 О проекте

YattoGram — полноценный веб-мессенджер с современным дизайном: регистрация и
авторизация, личные чаты в реальном времени (WebSocket), email-подтверждения,
сессии и профиль. Реализован без тяжёлых фреймворков — собственная MVC-структура
на PHP + нативный JavaScript.

## ✨ Возможности

- Регистрация / авторизация с валидацией и email-уведомлениями (PHPMailer + SMTP)
- Чаты в реальном времени на WebSockets (Ratchet)
- Сессии и восстановление доступа
- Персональные настройки профиля
- Адаптивный интерфейс

## 🧰 Стек

| Компонент | Технологии |
|---|---|
| Backend | PHP 8.1, PDO (MySQL), Ratchet (WebSocket-сервер) |
| Frontend | JavaScript (ES6+), CSS3, Twig 3 |
| Email | PHPMailer, SMTP |
| Инфраструктура | Composer, PSR-4 autoload |

## 🚀 Запуск

```bash
git clone https://github.com/LuckyDevv/AdFex_Chat_Dev.git
cd AdFex_Chat_Dev
composer install
# Настроить подключение к MySQL и SMTP в server/controllers/Configuration.php
# Поднять WebSocket-сервер:
php server/socket/ServerRunner.php
# Открыть index.php через веб-сервер (Apache/Nginx + PHP)
```

## 📂 Структура

```
index.php                    # точка входа
server/
  socket/                    # WebSocket-сервер (Ratchet): ServerRunner, SocketHandler
  controllers/               # контроллеры: конфигурация, авторизация, почта
  db_managers/               # работа с БД: аккаунты, сессии, чаты
src/
  js/                        # клиентская логика (auth, socket_auth, чаты)
  css/                       # стили интерфейса
```

## ⚠️ Безопасность

Демо-проект: перед публичным развертыванием вынесите доступы в переменные
окружения и включите HTTPS.

## 📜 Лицензия

MIT
