<?php
$json_data = '{"head":{},"def":[{"text":"pain","pos":"noun","ts":"peɪn","tr":[{"text":"боль","pos":"существительное","gen":"ж","syn":[{"text":"страдание","pos":"существительное","gen":"ср"},{"text":"горе","pos":"существительное","gen":"ср"},{"text":"огорчение","pos":"существительное","gen":"ср"},{"text":"мука","pos":"существительное","gen":"ж"},{"text":"мучение","pos":"существительное","gen":"ср"}],"mean":[{"text":"ache"},{"text":"misery"},{"text":"grief"},{"text":"agony"}],"ex":[{"text":"back pain","tr":[{"text":"боль в спине"}]},{"text":"mental pain","tr":[{"text":"душевное страдание"}]},{"text":"pains of hell","tr":[{"text":"муки ада"}]}]},{"text":"обезболивание","pos":"существительное","gen":"ср","mean":[{"text":"anesthesia"}]}]},{"text":"pain","pos":"verb","ts":"peɪn","tr":[{"text":"болеть","pos":"глагол","asp":"несов","mean":[{"text":"hurt"}]},{"text":"мучить","pos":"глагол","asp":"несов","mean":[{"text":"torment"}]},{"text":"стараться","pos":"глагол","asp":"несов","mean":[{"text":"try"}]},{"text":"огорчать","pos":"глагол","asp":"несов","mean":[{"text":"upset"}]}]},{"text":"pain","pos":"adjective","ts":"peɪn","tr":[{"text":"болевой","pos":"прилагательное","mean":[{"text":"painful"}],"ex":[{"text":"pain syndrome","tr":[{"text":"болевой синдром"}]}]},{"text":"больной","pos":"прилагательное","mean":[{"text":"patient"}]},{"text":"болеутоляющий","pos":"прилагательное","mean":[{"text":"painkiller"}],"ex":[{"text":"pain medication","tr":[{"text":"болеутоляющее средство"}]}]}]}]}';

echo sendDetailedOutput_new($json_data);

echo '<br/><br/>';

$json = json_decode($json_data, TRUE);
print("<pre>".print_r($json,true)."</pre>");

function sendDetailedOutput($article)
{
    $data = json_decode($article);
    $trans = $data->def[0]->tr[0]->text;
    $pos = $data->def[0]->tr[0]->pos;
    $syn = $data->def[0]->tr[0]->syn[0]->text;
    //Если синонима нет, выводим просто перевод и часть речи
    if (empty($syn)) {
        $result = $trans . ' (' . $pos . ').';
    } else {
        $syn_pos = $data->def[0]->tr[0]->syn[0]->pos;
        $result = $trans . ' (' . $pos . '), synonym - ' . $syn . ' (' . $syn_pos . ').';

    }
    return $result;
}

function sendDetailedOutput_new($article)
{
    $result = '';
    $data = json_decode($article);

    foreach ($data->def as $def) {
        $pos = $def->pos;
        $result = $result . "\n(" . $pos . ")";
        foreach ($def->tr as $tr) {
            $result = $result . " " . $tr->text;
        }
    }



    return $result;
}