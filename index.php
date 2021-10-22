<?php
/**
 * Using https://github.com/pematon/adminer-custom
 */

function adminer_object(): AdminerPlugin
{
    // Required to run any plugin.
    include_once "./plugins/plugin.php";

    // Plugins auto-loader.
    foreach (glob("plugins/*.php") as $filename) {
        include_once "./$filename";
    }

    // Specify enabled plugins here.
    $plugins = [
        new AdminerDatabaseHide(["mysql", "information_schema", "performance_schema"]),
        new AdminerTablesFilter(),
        new AdminerFloatThead,
        new AdminerDumpZip,
        new AdminerReadableDates,
        new AdminerDumpArray,
        new AdminerSimpleMenu(),
        new AdminerCollations(),
        new AdminerJsonPreview(),

        // AdminerTheme has to be the last one.
        new AdminerTheme(),
    ];


    return new AdminerPlugin($plugins);
}

// Include original Adminer or Adminer Editor.
$git_url = 'https://api.github.com/repos/vrana/adminer/releases/latest';
$useragent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';
$local_file_location = __DIR__ . '/adminer.php';
$status = [];
$latest = [];


/**
 * Get Adminer latest released version from GitHub API
 *
 * @param $url
 * @param $useragent
 * @return bool|mixed
 */
function get_adminer_latest_version($url, $useragent)
{
    if (in_array('curl', get_loaded_extensions())) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            // Do not use SSL verifications, this is to make it work in local self-signed installations (Laragon)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $data = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_errno = curl_errno($ch);
            if (isset($http_status) && $http_status === 503) {
                $status['error'] = "Curl HTTP Status 503";
                $status['status'] = 'fail';
            }

            if ($curl_errno) {
                $status['error'] = "Error in executing Curl.Error : " . $curl_errno;
                $status['status'] = 'fail';
            }

            curl_close($ch);
            $status['status'] = "OK, retrived";
            return $data;
        } catch (Exception $e) {
            $status['error'] = "Erreur. Message : " . $e->getMessage();
            $status['status'] = 'fail';
        }
    } else {
        $status['error'] = "Erreur. Message : Curl is not installed";
        $status['status'] = 'fail';
        return false;
    }
}

// Get latest version number from Github API
$latest_git_version = false;
try {
    //$latest_version = get_adminer_latest_version($git_url, $useragent);
    $git_info = json_decode(get_adminer_latest_version('https://api.github.com/repos/vrana/adminer/releases/latest', $useragent), true, 512, JSON_THROW_ON_ERROR);
    if (!empty($git_info)) {
        $latest_git_version = $git_info['tag_name'];
    } else {
        $latest_git_version = false;
    }
} catch (Exception $e) {
    $status['error'] = 'Erreur. Message : ' . $e->getMessage();
}

if (!empty($latest_git_version)) {
    $latest_version = str_replace('v', '', $latest_git_version);
} else {
    $latest_version = '';
}


// Get current local installed version
if (file_exists('current-version')) {
    $current_local_version = file_get_contents(__DIR__ . '/current-version');
} else {
    $current_local_version = '0.0.0';
}

// Check if a local version exists
if (file_exists($local_file_location)) {
    $local_file = true;
} else {
    $local_file = false;
}

// Check if local version is different from online, then download and update local version file
if ($local_file === false) {
    // Download latest version, from Github releases, and write adminer.php file to disk.
    if ($latest_git_version !== false) {
        $latest_version_url_download = 'https://github.com/vrana/adminer/releases/download/' . $latest_git_version . '/adminer-' . $latest_version . '.php';
        $get_initial_file = get_adminer_latest_version($latest_version_url_download, $useragent);
        $fp = fopen(__DIR__ . '/adminer.php', 'wb+');
        $result = fwrite($fp, $get_initial_file);
    } else {
        $local_file = false;
    }
}

if (!empty($latest_version) && ($current_local_version !== $latest_version)) {
    // New version found online, downloading new version
    // Write latest version to local version file
    $fp = fopen('./current-version', 'wb+');
    $version = fwrite($fp, $latest_version);

    // Download latest version, from Github releases, and write adminer.php file to disk.
    $latest_version_url_download = 'https://github.com/vrana/adminer/releases/download/' . $latest_git_version . '/adminer-' . $latest_version . '.php';
    $latest_adminer_file = get_adminer_latest_version($latest_version_url_download, $useragent);

    // Write locally latest version
    if ($latest_adminer_file !== false) {
        $fp = fopen(__DIR__ . '/adminer.php', 'wb+');
        $result = fwrite($fp, $latest_adminer_file);
    }

    fclose($fp);

    if (!isset($result)) {
        $status['error'] = 'Adminer latest version retrieval failed, local file not written, using the local latest version.<br>';
    }
} else {
    $status['error'] = "Adminer file is empty (missing content), not loaded.";
}


// File is written, continue loading, and make a check to see if the file is a empty one (curl error, on retrieval)
if (file_exists($local_file_location)) {
    include_once 'adminer.php';
} else {
    $status['error'] = "Adminer file is empty, not loaded. Cannot continue.";
}

$status_result = json_encode($status, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
var_dump($status_result);
