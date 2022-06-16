<?php
if ($argc < 2) {
    throw new Exception('Фаил не был передан');
}

$filePath = $argv[1];
$result = [
    'views' => 0,
    'urls' => 0,
    'traffic' => 0,
    'crawlers' => [
        'Google' => 0,
        'Bing' => 0,
        'Baidu' => 0,
        'Yandex' => 0,
    ],
    'statusCodes' => []
];
$uniqueUrls = [];

foreach (getRows($filePath) as $row) {
    $result['views']++;
    $explodeRow = explode('"', $row);

    $explodeDocumentUrlString = explode(' ', trim($explodeRow[1]));
    $documentUrl = $explodeDocumentUrlString[1];

    if (!in_array($documentUrl, $uniqueUrls)) {
        $uniqueUrls[] = $documentUrl;
        $result['urls']++;
    }

    $explodeAnswerCodeString = explode(' ', trim($explodeRow[2]));

    if (!is_numeric($explodeAnswerCodeString[0]) || !is_numeric($explodeAnswerCodeString[1])) { 
        throw new Exception('Неверный формат файла');
    }
    $answerCode = (int)$explodeAnswerCodeString[0];
    $documentSize = (int)$explodeAnswerCodeString[1];

    if (array_key_exists($answerCode, $result['statusCodes'])) {
        $result['statusCodes'][$answerCode]++;
    } else {
        $result['statusCodes'][$answerCode] = 1;
    }

    if ($answerCode === 200) {
        $result['traffic'] += $documentSize;
    }

    $explodeUserAgentString = $explodeAnswerCodeString = explode(')', trim($explodeRow[5]));
    $explodeUserAgent = explode('/', $explodeUserAgentString[1]);
    $userAgent = strtolower(trim($explodeUserAgent[0]));

    switch ($userAgent) {
        case 'googlebot':
            $crawlerBrowser = 'Google';
            break;
        case 'bingbot':
            $crawlerBrowser = 'Bing';
            break;
        case 'baidubot':
            $crawlerBrowser = 'Baidu';
            break;
        case 'yandexbot':
            $crawlerBrowser = 'Yandex';
            break;
        default:
            $crawlerBrowser = null;
            break;
    }
    
    if ($crawlerBrowser !== null) {
        $result['crawlers'][$crawlerBrowser]++;
    }  
}
echo json_encode($result);

function getRows($file) {
    $handle = fopen($file, 'rb');
    if (!$handle) {
        throw new Exception('Не удалось открыть файл');
    }
   
    while (!feof($handle)) {
        yield fgets($handle);
    }
   
    fclose($handle);
}
