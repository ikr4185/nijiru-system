<?php
/**
 * @see https://gist.github.com/ryomatsu/f67938a8af76377b2888f79fca592253
 */

// ログ内容を取得する
$string = "";
$date = date("Y-m-d", strtotime('-1 day'));
$logs = file("/home/njr-sys/public_html/cli/logs/irc/irc-logs_{$date}.dat");

foreach ($logs as $log) {

    // カシマの発言は除外
    if (strpos($log, "(KASHIMA-EXE)") !== false) {
        continue;
    }

    // リンクも外す
    if (strpos($log, "http") !== false) {
        continue;
    }

//    echo ".";
    usleep(3000);

    preg_match("/^(.*?) - (.*?) - (.*?)$/", $log, $matches);
    if (isset($matches[3])) {

        $string .= $matches[3];
        if (mb_strpos($string, -1) !== "。") {
            $string .= "。";
        }

    }
}

// 一行にまとめる
$string = str_replace(array(" ", "　", "&quot;", "\n", "」"), "", strip_tags($string));

// 形態素解析の実行
$output = array();
exec("sh /home/njr-sys/public_html/cli/sh/MarkovTest.sh \"{$string}\"", $output);

// 配列の整形
$result = array();
$rawTexts = array();
$nouns = array();
foreach ($output as $line) {
    
    $array = explode("\t", $line);
    $result[] = $array;
    $rawTexts[] = $array[0];

    // 名詞句の抽出(不正確なため、二文字以上のもの)
    if (isset($array[3])) {
        if (mb_strpos($array[3], "名詞") !== false && mb_strlen($array[0]) > 3) {
            $nouns[$array[0]] = $array[0];
        }
    }
}

// マルコフ連鎖テーブルの生成
$markov = null;
if (count($rawTexts) > 2) {
    for ($i = 2; $i < count($rawTexts); $i++) {
        if ($i < count($rawTexts)) {
            $markov[$rawTexts[$i - 2]][$rawTexts[$i - 1]][] = $rawTexts[$i];
        }
    }
} else {
    print "文章が短すぎてマルコフ連鎖が行えません。<br />";
    exit();
}

// マルコフ連鎖で文章生成

//// 文章出だしを接頭語前に
//$pre1 = $rawTexts[0];

// 出だしをランダムな名詞にする
$pre1 = array_rand($nouns);

// 接頭語後ろは、接頭語前に続く文字からランダム選択
$pre2 = array_rand($markov[$pre1]);
// 接尾語は選択できる中からランダムに選択
$rand = rand(0, (count($markov[$pre1][$pre2]) - 1));
$suf1 = $markov[$pre1][$pre2][$rand];
$string = $pre1 . $pre2 . $suf1;


// 選択できる中からランダムに選択
for ($i = 0; $i < 100; $i++) {

    $pre1 = $pre2;
    if (!isset($markov[$pre1])) {
        continue;
    }

    $pre2 = $suf1;
    if (!isset($markov[$pre1][$pre2])) {
        continue;
    }

    $rand = rand(0, (count($markov[$pre1][$pre2]) - 1));
    if (!isset($markov[$pre1][$pre2][$rand])) {
        continue;
    }

    $suf1 = $markov[$pre1][$pre2][$rand];
    if ($suf1 == "EOS") {
        continue;
    }

    usleep(10000);
    $string .= $suf1;

    //。が出たら終わり
    if (mb_strpos($suf1, "。") !== false) {
        break;
    }
}

echo "\n\n";
print $string . "\n";
exit;

