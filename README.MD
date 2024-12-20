# Проект по работе с данными

## Задача
Необходимо стянуть все данные по описанным эндпоинтам и сохранить в БД.

## Доступ к бд на хостинге
- **URL**: [phpMyAdmin](https://fvh1.spaceweb.ru/phpMyAdmin/index.php)
- **Логин**: `vsevolodso`
- **Пароль**: `Password0`
- **Имя базы данных**: `vsevolodso`

## Параметры запросов
Для работы с таблицами использовались следующие параметры:
- `dateFrom=2024-01-01`
- `dateTo=2024-12-31`
- `limit=500`

### Особые условия
- Во всех таблицах, кроме таблицы `stocks`, использовались указанные параметры. Для таблицы `stocks` API возвращает данные только при текущем значении `dateTo`, поэтому запускался с параметрами по умолчанию, указанными в коде (например, `date('Y-m-d')` и т.д.).

## Описание реализации
Во время разработки работа с таблицами шла с помощью миграций. Для упрощения выполнения технического задания (ТЗ) была использована общая модель для трех из четырех таблиц. Обратите внимание, что в некоторых колонках были допущены ошибки с количеством цифр после запятой. Я прошу проявить понимание, так как не стал исправлять эти ошибки, поскольку ТЗ не оплачивается, и не хотел тратить на него лишнее время.

### Структура кода
Структуру кода оценивайте только по сущности продаж (`sales`), так как только для них была разработана отдельная сущность.

## Маршруты
Отдельный сервер есть только у бд. Поэтому маршруты состоят из url, полученный после запуска `php artisan serve` + ('/fetch-incomes' или '/fetch-sales' или '/fetch-stocks' или '/fetch-orders') + обязательные параметры в GET-запросе (для всего кроме '/fetch-stocks', `dateFrom` и `dateTo`). Пример запроса: `http://127.0.0.1:8000/fetch-incomes?dateFrom=2024-01-01&dateTo=2024-12-31&limit=10` - такой запрос добавить 10 записей в таблицу `incomes`

## Заключение
Если у вас есть вопросы или требуется дополнительная информация, не стесняйтесь обращаться.
