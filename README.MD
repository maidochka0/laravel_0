# Проект по работе с данными

## Задача
Необходимо стянуть все данные по описанным эндпоинтам и сохранить в БД.

## Особенности развертывания
- использовать docker-compose
- в cron_src нужно вручную задать адрес сервера для ежедневного обновления данных

## Доступы
- сервис приложения называется `laravel_0_app_1`
- логин:пароль для бд - user:password
- порты. phpmyadmin:8080 приложение:9001
- остальную информацию можно посмотреть в docker-compose или .env

## Начало работы
### создание первых данных компании
- для создание записей в бд для некоторых вещей нужны внешнии ключи. рекомендуемый порядок создание записей такой же, как у миграций. я довольно поздно пришел к тому, что внешние ключи проще задавать контрольной миграцией, поэтому вместо этого просто задал нужный порядок выполнения миграций. 
- сигнатуры смотреть в `app/Console/Commands/`
- для задачи внешних ключей нужны `id` из таблиц. исключение: при создании токена, `id` типа токена ищется по названию.
### использоване токенов и аккаунтов
- при добавление записей из АПИ, всем записям задается `account_id` текущего пользователя(`NULL`, если ключ пользователя не зарегестрирован в бд)
- выбор апи-сервисов в коде не реализовывал. но они используются при создании типов токенов, что может помочь в отладке
- при передаче через GET ключа, бд будет искать запись токена по значению ключа и использовать `account_id` , к которому привязали токен при создании.
- можно создать аккаунт(и предварительно компанию, которой он принадлежит) для cron-процесса и задать его в константе `CRON_ACCOUNT_ID`, чтобы в бд, при ежедневном обновлении, у `account_id` записей не стоял `NULL` в значении
### пример работы по задумке
- создается компания и ее аккаунт, а так же апи-сервис и допступный ему тип токена.
- создается токен, по которому будет распознаваться пользователь приложения
- при обращении к апи, теперь достаточно выполнить те же действия, что и для первой версии этого тествого задания, чтобы в бд данные записывались от имени пользователя токена, и некоторые данные уходили в логи и консоль

## Маршруты
- маршруты состоят из url сервера + необязательные параметры в GET-запросе. Пример запроса: http://193.233.114.253:9001/fetch-incomes?dateFrom=2024-01-01 - такой запрос добавит/обновит записи за последний год в таблицу `incomes`

## Структура кода
### controller
- все функции контролера лежат в `app/Http/Controllers/ApiDataController.php`
- команды начинающиеся с `fetch-` добавляют/обновляют записи в бд. для этого они затирают старые записи по аккаунт_айди и указаным датам, используют `add($table, $data, $token)`, который я расширил парой функций для работы с `account_id`
- dailyUpdate() используется в cron
- `handleRateLimiting(Request $request)` нужен для работы с ошибками типа "Too many requests"
- `debugMess($mess)` делает запись в логи, а если в конастанте `DEBUG_MOD` значение `1`, то сообщение дублируется еще и в консоль
- `getData($accountId, $table, $days=false, $limit=false)` возращает данные из указанной таблицы по `accaunt_id`, который их добавил. опционально можно задать получение только свежих данных за последние `$days` дней или лимит на количество возвращаемых записей
### model
- в моделе задаются отношения между сущностями
- в моделе `All` можно получить ключ по умолчанию - им сейчас и пользуется весь контроллер. а еще список таблиц данных - это нужно для функции `dailyUpdate()`.
### debug
- я не упарывался в подробную отладку, поэтому ее получилось по минимуму
- во многих функциях контроллера есть обработка ошибок
- есть возможность добавлять сообщения в консоль и ввести логи через `debugMess()`
### cron
- докер добавляет крон-задачу из файла crontab, она исполняет cron_src.php и перенаправляет вывод в cron.log в 8 утра и вечера
- cron_src.php использует маршрут, вызывающий функцию `dailyUpdate()`, поочередно вызывающие 4 `fetch-`функции с текущей датой из контроллера
- во все 4 таблицы добавляются данные за текущее число(во время моих вызовов, свежие данные были только в stocks, но это из-за того, какие данные возвращает api)

## Заключение
Если у вас есть вопросы или требуется дополнительная информация, не стесняйтесь обращаться.
