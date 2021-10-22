<?php
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
        try 
        {
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
        //$curl_errno = curl_errno($ch);
        if (isset($http_status) && $http_status === 503) {
            die("HTTP Status == 503 <br/>");
        }
        
        if ($curl_errno) {
            die("Error in executing Curl : $curl_errno <br/>");
        }
        
        curl_close($ch);

        return $data;
        } catch (Exception $e) {
        $status['error'] = 'Erreur. Message : ' . $e->getMessage();
        return false;
        }
    } else {
        die("Curl is not installed");
        return false;
    }
}

$git_url = 'https://api.github.com/repos/vrana/adminer/releases/latest';
$useragent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

$version = get_adminer_latest_version($git_url,$useragent);
echo 'Version: <br>';
var_dump($version);
