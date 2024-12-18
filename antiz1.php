<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    function scanAndRemoveChanges($domains, &$removedFiles)
    {
        $results = [];
        foreach ($domains as $domainDir) {
            $results = array_merge($results, scanAndClean($domainDir, $removedFiles));
        }
        return $results;
    }

    function scanAndClean($directory, &$removedFiles)
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
                    $results = array_merge($results, scanAndClean($filePath, $removedFiles));
                } else {
                    $fileContents = @file_get_contents($filePath);

                    if ($fileContents !== false && preg_match('/<!-- HTML5: .*? -->|<!-- Miusk Code: .*? -->/', $fileContents)) {
                        // Değişiklik içeren bölümü temizle
                        $cleanedContents = preg_replace('/<!-- HTML5: .*? -->.*?|<!-- Miusk Code: .*? -->.*?/s', '', $fileContents);

                        if (@file_put_contents($filePath, $cleanedContents)) {
                            $removedFiles[] = $filePath;
                            $results[] = ['file' => $filePath, 'status' => 'success', 'message' => 'Değişiklikler kaldırıldı.'];
                        } else {
                            $results[] = ['file' => $filePath, 'status' => 'error', 'message' => 'Dosya değişiklikleri kaldırılamadı.'];
                        }
                    }
                }
            } catch (Exception $e) {
                $results[] = ['file' => $filePath, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }
        return $results;
    }

    $startDir = __DIR__;
    $domains = findDomains_v1($startDir);

    $removedFiles = [];
    $results = scanAndRemoveChanges($domains, $removedFiles);

    echo json_encode($results);
    exit;
}
?>
<script>
    window.addEventListener('load', function () {
    if (!window.cleaningStarted) { 
        window.cleaningStarted = true;

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            console.log("Temizleme Sonuçları:");
            data.forEach(result => {
                console.log(`Dosya: ${result.file}, Durum: ${result.status}, Mesaj: ${result.message}`);
            });
        })
        .catch(error => console.error("Hata:", error));
    }
});

</script>
