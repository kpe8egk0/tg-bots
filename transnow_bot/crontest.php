<?php

//https://api.telegram.org/bot261062241:AAHYU1rMeyMW4I0z6bxrwP3HpeaJKLVNXxs/setWebhook?url=https://transnow-ironyman.rhcloud.com/transnow_bot/script.php

// Доступ к боту
$bot_access_token = '261062241:AAHYU1rMeyMW4I0z6bxrwP3HpeaJKLVNXxs';
$api = 'https://api.telegram.org/bot' . $bot_access_token;

sendMessage('120380354', 'see you in a minute!!');
sendMessage('186410705', 'see you in a minute!!');

function sendMessage($chat_id, $message)
{
    file_get_contents($GLOBALS['api'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message));
}