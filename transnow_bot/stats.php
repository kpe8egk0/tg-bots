<?php
header('Content-Type: text/html; charset=utf-8');

$n = 100;

echo 'Всего запросов: ' . getLookupTotalAmount() . '<br/>';
echo 'Всего статей: ' . getArticleTotalAmount() . '<br/>';
echo 'Всего пользователей: ' . getUserTotalAmount() . '<br/><br/>';
echo 'Последние '. $n . ' запросов: <br/>' . getLookupLastN($n);

function db()
{
    define('DB_HOST', getenv('MYSQL_SERVICE_HOST'));
    define('DB_PORT', getenv('MYSQL_SERVICE_PORT'));
    define('DB_USER', getenv('MYSQL_USER'));
    define('DB_PASS', getenv('MYSQL_PASSWORD'));
    define('DB_NAME', 'transnow');
    $dsn = 'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8';
    $dbh = new PDO($dsn, DB_USER, DB_PASS);
    return $dbh;
}

function getLookupTotalAmount() {
    $db = db();
    $stmt = $db->prepare('SELECT COUNT(*) FROM lookup');
    $stmt->execute();

    $output = $stmt->fetchColumn();

    return $output;
}

function getArticleTotalAmount() {
    $db = db();
    $stmt = $db->prepare('SELECT COUNT(*) FROM article');
    $stmt->execute();

    $output = $stmt->fetchColumn();

    return $output;
}

function getUserTotalAmount() {
    $db = db();
    $stmt = $db->prepare('SELECT COUNT(*) FROM user');
    $stmt->execute();

    $output = $stmt->fetchColumn();

    return $output;
}

function getLookupLastN($n) {
    $db = db();
    $stmt = $db->prepare('SELECT lkp.date as date, lkp.user as user, lkp.input_text as input_text, lkp.output_text as output_text, rtcl.input_text as flag FROM lookup lkp LEFT JOIN article rtcl ON lkp.input_text = rtcl.input_text ORDER BY id DESC LIMIT :n');
    $stmt->bindParam(':n', $n, PDO::PARAM_INT);
    $stmt->execute();

    $output = '';
    foreach ($stmt as $row) {
        if ($row['flag'] == '') {
            $flag = '-';
        } else {
            $flag = '+';
        }
        $output = $output . '<br/>[' . $row['date'] . '] (' . $flag . ') <a href="https://web.telegram.org/#/im?p=@' . $row['user'] . '">' . $row['user'] . '</a>: "' . $row['input_text'] . '"';// -> "' . $row['output_text'] . '"';
    }

    return $output;
}