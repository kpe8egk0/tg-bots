<?php
//dogyyy = 186410705
//dallas = 120380354
$bot_access_token = '261062241:AAHYU1rMeyMW4I0z6bxrwP3HpeaJKLVNXxs';
$api = 'https://api.telegram.org/bot' . $bot_access_token;

//Отправка
$chat_ids = get_chat_ids();
$msg_to_all = 'Друзья, просим прощения за технические неполадки в работе нашего бота. Мы всё наладили, и переводом снова можно пользоваться без всяких "Incorrect input language! Please, try again." ;) В случае возникновения каких-либо вопросов, предложений или проблем, пишите нам @transnowsupport или по адресу transnowapplication@gmail.com';
if (!empty($chat_ids))
{
    foreach ($chat_ids as $ids)
    {
        sendMessage($ids['ids'], $msg_to_all);
        update_flag($ids['ids']);
    }
}
else
{
    sendMessage('186410705', 'Done!');
    sendMessage('120380354', 'Done!');
}
//Функции
// Базовая функция доступа к БД
function db()
{
    define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST'));
    define('DB_PORT', getenv('OPENSHIFT_MYSQL_DB_PORT'));
    define('DB_USER', getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
    define('DB_PASS', getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
    define('DB_NAME', getenv('OPENSHIFT_GEAR_NAME'));
    $dsn = 'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8';
    $dbh = new PDO($dsn, DB_USER, DB_PASS);
    return $dbh;
}

//Получить 20 chat_id, которым сообщения ещё не были отправлены
function get_chat_ids()
{
    $db = db();
    $stmt = $db->prepare('SELECT chat_id as ids FROM user WHERE sent_flag = 0 LIMIT 20');
    $stmt->execute();
    $row = $stmt->fetchAll();
    return $row;
}

//Изменить флаг отправки с 0 на 1
function update_flag($chat_id)
{
    $db = db();
    $stmt = $db->prepare('UPDATE user SET sent_flag = 1 WHERE chat_id = :chat_id');
    $stmt->bindParam(':chat_id', $chat_id);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row;
}

// Отправка сообщения
function sendMessage($chat_id, $message)
{
    file_get_contents($GLOBALS['api'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message));
}