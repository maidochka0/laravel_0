<?php
// Данные для подключения
$host = 'FVH1.spaceweb.ru'; // Хост для подключения
$db_name = 'vsevolodso';    // Имя базы данных
$username = 'vsevolodso'; // Ваше имя пользователя
$password = 'Password0';       // Ваш пароль

// Создание подключения
$mysqli = new mysqli($host, $username, $password, $db_name);

// Проверка подключения
if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

echo "Успешное подключение к базе данных";

// Закрытие подключения
$mysqli->close();
?>
