<?php
// https://api.telegram.org/bot261062241:AAHYU1rMeyMW4I0z6bxrwP3HpeaJKLVNXxs/setWebhook?url=https://transnow-ironyman.rhcloud.com/transnow_bot/test_bot2.php
$trans = array();
$source = 'yandex';
$yandex_dict_key = 'dict.1.1.20160819T080857Z.a21f9f5c92e0e7b9.ab24906e2b9b24a62bede201ca3067abadaf5752';
$yandex_trans_key = 'trnsl.1.1.20160906T144940Z.7b9bdff453462ecd.bcabb5b47a3afe432e57931793362ad73e47898f';

$access_token = '261062241:AAHYU1rMeyMW4I0z6bxrwP3HpeaJKLVNXxs';
$api = 'https://api.telegram.org/bot' . $access_token;

$input_json = file_get_contents('php://input');
$output = json_decode($input_json, TRUE);

$chat_id = $output['message']['chat']['id'];
$message = $output['message']['text'];
$username = $output['message']['from']['first_name'].' '.$output['message']['from']['last_name'];
$lang = 'ru-en';

$reply = incoming($source, $message, $yandex_dict_key, $yandex_trans_key, $lang, $ui);
file_get_contents($GLOBALS['api'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($reply));
exit();

//----------------------------------------------- Functions ----------------------------------------------

// Обработка входящего сообщения
function incoming($source, $message, $yandex_dict_key, $yandex_trans_key, $lang, $ui)
{
    $array_msg = explode(' ', $message, 2);
    $command = $array_msg[0];
    $msg = $array_msg[1];
    if (!empty($msg)) {
        $article = getArticleFromSource($source, $lang, $msg, $yandex_dict_key, $ui);
        if ($command == '/full') {
            $reply = full_output($msg, $article);
        } elseif ($command == '/short') {
            $reply = short_output_detailed($msg, $article);
        } elseif ($command == '/def') {
            $reply = $array_msg[1] . ' language is ' . lang_def($msg, $yandex_trans_key);
        } elseif ($command == '/help') {
            $reply = "Help message";
        } else {
            $reply = "Unknown command or More then one word entered!";
        }

    }
    else {
        if ($command == '/help') {
            $reply = "Help message";
        }
        elseif ($command == '/full'){
            $reply = "Word for translation required!";
        }
        elseif ($command == '/short'){
            $reply = "Word for translation required!";
        }
        elseif ($command == '/def'){
            $reply = "Word for language definition required!";
        }
        else {
            $article = getArticleFromSource($source, $lang, $command, $yandex_dict_key, $ui);
            $reply = shortest_output($article);
        }
    }
    return $reply;
}
// Получение статьи из внешнего источника
function getArticleFromSource($source, $lang, $input_text, $key, $ui)
{
    $url = sprintf('https://dictionary.yandex.net/api/v1/dicservice.json/lookup?key=%s&lang=%s&text=%s&ui=%s&flags=2', $key, $lang, $input_text, $ui);
    $json_data = file_get_contents($url);
    return $json_data;
}

// Определение языка вводимого слова
function lang_def($message, $key)
{
    $url = sprintf('https://translate.yandex.net/api/v1.5/tr.json/detect?hint=en,ru&key=%s&text=%s', $key, $message);
    $json_data = file_get_contents($url);
    $data = json_decode($json_data);
    $reply = $data->lang;
    return $reply;
}

// Несколько вариантов перевода
function full_output($input, $article)
{
    $data = json_decode($article);
    for ($i = 0; $i<=4; $i++) {
        $trans[$i] = $data->def[0]->tr[$i]->text;
    }
    $transfiltered = array_filter ($trans);
    $reply = 'The word "'.$input.'" translates like: '.implode(', ', $transfiltered).'.';
    return $reply;
}

// Один Вариант перевода подробный (с частью речи и синонимами, если есть)
function short_output_detailed($input, $article)
{
    $data = json_decode($article);
    $trans = $data->def[0]->tr[0]->text;
    $pos = $data->def[0]->tr[0]->pos;
    $syn = $data->def[0]->tr[0]->syn[0]->text;
    if (empty($syn))
    {
        $reply = $trans.' ('.$pos.').';
    }
    else
    {
        $syn_pos = $data->def[0]->tr[0]->syn[0]->pos;
        $reply = $trans.' ('.$pos.'), синоним - '.$syn.'. ('.$syn_pos.').';
    }
    return $reply;
}

// Один Вариант перевода (самый короткий, просто перевод)
function shortest_output($article)
{
    $data = json_decode($article);
    $reply = $data->def[0]->tr[0]->text;
    return $reply;
}