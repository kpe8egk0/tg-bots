<?php

//https://api.telegram.org/bot261062241:AAHYU1rMeyMW4I0z6bxrwP3HpeaJKLVNXxs/setWebhook?url=https://transnow-ironyman.rhcloud.com/transnow_bot/script.php
//dogyyy = 186410705
// Доступ к боту
$bot_access_token = '261062241:AAHYU1rMeyMW4I0z6bxrwP3HpeaJKLVNXxs';
$api = 'https://api.telegram.org/bot' . $bot_access_token;

// Доступ к словарю Яндекса
$yandex_dict_key = 'dict.1.1.20160819T080857Z.a21f9f5c92e0e7b9.ab24906e2b9b24a62bede201ca3067abadaf5752';

//Доступ к переводчику Яндекса
$yandex_trans_key = 'trnsl.1.1.20160906T144940Z.7b9bdff453462ecd.bcabb5b47a3afe432e57931793362ad73e47898f';

$input = json_decode(file_get_contents('php://input'), TRUE);

$chat_id = $input['message']['chat']['id'];
$message = $input['message']['text'];
$username = $input['message']['from']['username'];
//Проверка на пустоту last_name
if (!empty($input['message']['from']['last_name']))
{
    $full_name = $input['message']['from']['first_name'] . ' '. $input['message']['from']['last_name'];
}
else
{
    $full_name = $input['message']['from']['first_name'];
}

if ($chat_id == getManualChatID()['value']) {
    sendMessage($chat_id, 'Ok!');
    exit();
}

// Проверка наличия пользователя в БД. Если пользователь не найден, выполняется добавление.
$user = getUser($username);
if (!isset($user['user'])) {
    addUser($username, $chat_id, $message);
}

// Проверка актуальности chat_id пользователя.
if ($user['chat_id'] != $chat_id) {
    updateUsersChatID($username, $chat_id);
}

$message = strtolower($message);
switch ($message) {
    case '/start':
        sendMessage($chat_id, 'Привет! Чтобы начать, пожалуйста, напиши любое слово или предложение для перевода на русском или английском языке. В случае возникновения каких-либо вопросов, предложений или проблем, пиши нам @transnowsupport или по адресу transnowapplication@gmail.com Hi there! To start please write any word or sentence for translation in russian or english! Should you have any questions, proposals or problems, please do not hesitate to contact us @transnowsupport or transnowapplication@gmail.com');
        exit();
    case '/help_ru':
        sendMessage($chat_id, 'Этот бот может помочь тебе с переводом слова или предложения с русского на английский и наоборот. Просто напиши слово или предложение на нужном языке и получишь перевод. В случае возникновения каких-либо вопросов, предложений или проблем, пиши нам @transnowsupport или по адресу transnowapplication@gmail.com');
        exit();
    case '/help_en':
        sendMessage($chat_id, 'This bot can help you to translate some word or a sentence from russian to english and conversely. You just need to write the word or sentence using the language you need. Should you have any questions, proposals or problems, please do not hesitate to contact us @transnowsupport or transnowapplication@gmail.com');
        exit();
    case '/lepra':
        sendMessage($chat_id, 'Привет, %username%!');
        exit();
    /*ОСТОРОЖНО! Отсылает сообщение $message Всем пользователям из таблицы user!
    case '/admin_send_to_all_code!@#3647584943568':
        $chat_ids = get_chat_ids();
        $i = 0;
        //sendMessage('186410705', count($chat_ids));
        //$msg_to_all = 'Друзья, просим прощения за технические неполадки в работе нашего бота. Мы всё наладили и переводом снова можно пользоваться без всяких "Incorrect input language! Please, try again." ;) В случае возникновения каких-либо вопросов, предложений, или проблем, пишите нам по адресу transnowapplication@gmail.com';
        foreach ($chat_ids as $ids)
        {
            sendMessage('186410705', $i);
            $i++;
            sendMessage('186410705', $ids['ids']);
            //sendMessage($ids['ids'], $msg_to_all);
        }
        exit();*/
    default:
        break;
}


// Определение языка ввода и языковогокода перевода
$message = urlencode($message);
$outputLangCode = '';
$inputLangCode = detectInputLang($message, $yandex_trans_key);
switch ($inputLangCode) {
    case 'ru':
        $outputLangCode = 'ru-en';
        break;
    case 'en':
        $outputLangCode = 'en-ru';
        break;
    default:
        $code = test_detect_code($message, $yandex_trans_key);
        $test_json = test_detect_json($message, $yandex_trans_key);
       // $serv_msg = 'code: '.$code. 'detected lang: '.$inputLangCode.' URL = '.$test_json;
       // sendMessage('186410705', $serv_msg);
       // sendMessage('120380354', $serv_msg);
        $outputLangCode = 'error';
        addLookup($username, $input['message']['text'], $outputLangCode, $chat_id);
        if (!empty($inputLangCode))
        {
            sendMessage($chat_id, 'Unsupported language ' . strtoupper($inputLangCode) . '! Please use English or Russian. Неподдерживаемый язык ' . strtoupper($inputLangCode) . '! Пожалуйста, используй Английский, или Русский.');
        }
        else
        {
            sendMessage($chat_id, 'Language detection error! Please try again using English or Russian. Ошибка определения языка! Пожалуйста, попробуй ещё раз, используя Английский или Русский.');
        }
            exit();
}

$output_json = getArticleFromSource('yandex_dict', $outputLangCode, $message, $yandex_dict_key);
// Заглушка, если перевод не найден
$decoded_json = json_decode($output_json);
$trcheck = $decoded_json->def[0]->tr[0]->text;
if (empty($trcheck))
{
    $output_json = getArticleFromSource('yandex_trans', $outputLangCode, $message, $yandex_trans_key);
    $decoded_json = json_decode($output_json);
    if ($decoded_json->text[0] == $message) {
        $output_text = "Translation was not found";
    }
    else {
        $output_text = $decoded_json->text[0];
    }
}
if (!empty($trcheck)) {
    addArticle($message, $output_json, $outputLangCode);
    $output_text = sendDetailedOutput($output_json, $inputLangCode);
}
addLookup($username, $input['message']['text'], $outputLangCode, $chat_id);
sendMessage($chat_id, $output_text);

// Функции
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

// Получение пользователя по имени
function getUser($user)
{
    $db = db();
    $stmt = $db->prepare('SELECT * FROM user WHERE user = :user');
    $stmt->bindParam(':user', $user);
    $stmt->execute();
    $row = $stmt->fetch();

    return $row;
}

// Регистрация нового пользователя
function addUser($user, $chat_id, $text_temp)
{
    if ($user != '') {
        //sendMessage('120380354', 'New user: @' . $user . ' (chat_id: ' . $chat_id . '): ' . $text_temp); //kpe8egk0
        sendMessage('186410705', 'New user: @' . $user . ' (chat_id: ' . $chat_id . '): ' . $text_temp);
    }
    $db = db();
    $stmt = $db->prepare('INSERT INTO user (user, reg_date, chat_id) VALUES (:user, NOW(), :chat_id)');
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':chat_id', $chat_id);
    $stmt->execute();
}

// Обновление chat_id пользователя
function updateUsersChatID($user, $chat_id)
{
    $db = db();
    $stmt = $db->prepare('UPDATE user SET chat_id = :chat_id WHERE user = :user');
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':chat_id', $chat_id);
    $stmt->execute();
}


// Добавление статьи в БД
function addArticle($input_text, $article, $lang_code)
{
    $db = db();
    //$encode = $db->prepare('SET NAMES "utf8"');
    //$encode->execute();
    $stmt = $db->prepare('INSERT INTO article (input_text, article, lang_type_code) VALUES (:input_text, :article, :lang_code)');
    $stmt->bindParam(':input_text', $input_text);
    $stmt->bindParam(':article', $article);
    $stmt->bindParam(':lang_code', $lang_code);
    $stmt->execute();
}

// Добавление данных о поиске
function addLookup($user, $input_text, $lang_code, $chat_id)
{
    $output_text = '';
    $db = db();
    $stmt = $db->prepare('INSERT INTO lookup (user, input_text, lang_code, date, output_text, chat_id) VALUES (:user, :input_text, :lang_code, NOW(), :output_text, :chat_id)');
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':input_text', $input_text);
    $stmt->bindParam(':lang_code', $lang_code);
    $stmt->bindParam(':output_text', $output_text);
    $stmt->bindParam(':chat_id', $chat_id);
    $stmt->execute();
}

// Отправка сообщения
function sendMessage($chat_id, $message)
{
    file_get_contents($GLOBALS['api'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message));
}

// Определение языка вводимого слова (работает только через Яндекс переводчик)
function detectInputLang($message, $key)
{
    //$message_for_link = str_replace(' ', '%20', $message);
    //hint - это предполагаемые языки. Пока что оставил en, ru. Можно подумать над этим моментом ещё.
    $url = sprintf('https://translate.yandex.net/api/v1.5/tr.json/detect?hint=en,ru&key=%s&text=%s', $key, $message);
    $json_data = file_get_contents($url);
    $data = json_decode($json_data);
    $result = $data->lang;
    return $result;
}
//Служебные функции
function test_detect_code($message, $key)
{
    $url = sprintf('https://translate.yandex.net/api/v1.5/tr.json/detect?hint=en,ru&key=%s&text=%s', $key, $message);
    $json_data = file_get_contents($url);
    $data = json_decode($json_data);
    $result = $data->code;
    return $result;
}
function test_detect_json($message, $key)
{
    $url = sprintf('https://translate.yandex.net/api/v1.5/tr.json/detect?key=%s&text=%s', $key, $message);
    // $json_data = file_get_contents($url);
    // $data = json_decode($json_data);
    return $url;
}
function get_chat_ids()
{
    $db = db();
    $stmt = $db->prepare('SELECT chat_id as ids FROM user LIMIT 100000000');
    $stmt->execute();
    $row = $stmt->fetchAll();
    return $row;
}

// Вывод нескольких вариантов перевода
function sendFullOutput($input, $article)
{
    $data = json_decode($article);
    for ($i = 0; $i <= 4; $i++) {
        $trans[$i] = $data->def[0]->tr[$i]->text;
    }
    $transfiltered = array_filter($trans);
    $result = 'The word "' . $input . '" translates like: ' . implode(', ', $transfiltered) . '.';
    return $result;
}

// Вывод одного вариант перевода с частью речи и синонимом, если есть
function sendDetailedOutput($article, $inputLangCode)
{
    $result = '';
    $data = json_decode($article);

    foreach ($data->def as $def) {
        $commaFlag = false;
        $pos = $def->pos;
        $result = $result . "\n(" . $pos . ")";
        foreach ($def->tr as $tr) {
            if ($commaFlag)
                $result = $result . ",";
            $result = $result . " " . $tr->text;
            $commaFlag = true;
        }
    }

    return $result;
}

// Вывод одного варианта перевода
function sendShortOutput($article)
{
    $data = json_decode($article);
    $result = $data->def[0]->tr[0]->text;
    return $result;
}

// Получение статьи из внешнего источника
function getArticleFromSource($source, $lang, $input_text, $key)
{
    $json_data = '';
    switch ($source) {
        case 'yandex_trans':
            $url = sprintf('https://translate.yandex.net/api/v1.5/tr.json/translate?key=%s&lang=%s&text=%s', $key, $lang, $input_text);
            $json_data = file_get_contents($url);
            break;
        case 'yandex_dict':
            $url = sprintf('https://dictionary.yandex.net/api/v1/dicservice.json/lookup?key=%s&lang=%s&text=%s', $key, $lang, $input_text);
            $json_data = file_get_contents($url);
            break;
        default:
            break;
    }

    return $json_data;
}

// Получение ID чата на ручном управлении
function getManualChatID()
{
    $code = 'manual_chat_id';

    $db = db();
    $stmt = $db->prepare('SELECT value FROM param WHERE code = :code');
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    $row = $stmt->fetch();

    return $row;
}