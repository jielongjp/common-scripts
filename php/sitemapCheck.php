<?php

echo "XML sitemap URL: ";
$sitemap_url = trim(fgets(STDIN));

$headers = get_headers($sitemap_url, 1);

// .xml file or response header is xml type
if (
    filter_var($sitemap_url, FILTER_VALIDATE_URL) &&
    pathinfo($sitemap_url, PATHINFO_EXTENSION) === 'xml' ||
    isset($headers['Content-Type']) &&
    $headers['Content-Type'] === 'application/xml' || $headers['Content-Type'] === 'text/xml'
) {
    $urls = getUrlsFromSitemap($sitemap_url);
    displayStatusCodes($urls);
} else {
    echo "Invalid XML file.\n";
    return;
}

function getUrlsFromSitemap($sitemap_url) {
    $xml = simplexml_load_file($sitemap_url);
    $urls = [];

    foreach ($xml->url as $url) {
        $urls[] = (string)$url->loc;
    }

    return $urls;
}

function getStatusCode($url) {
    usleep(100);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $statusCode;
}

function displayStatusCodes($urls) {

    $hyphen_divider = "\n--------------------------\n";
    $status_success_urls = [];
    $status_other = [];
    $success_status = [
        200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
        300, 301, 302, 303, 304, 305, 306, 307, 308
    ];

    echo $hyphen_divider;
    echo "starting sitemap check";
    echo $hyphen_divider;

    foreach ($urls as $url) {
        $statusCode = getStatusCode($url);

        // print different colors for http status
        if ($statusCode === 200) {
            echo "\033[0m$url - $statusCode\n";
        } elseif (strpos($statusCode, '4') === 0) {
            echo "\033[31m$url - $statusCode\n";
        } elseif (strpos($statusCode, '5') === 0) {
            echo "\033[33m$url - $statusCode\n";
        } else {
            echo "\033[36m$url - $statusCode\n";
        }


        if (in_array($statusCode, $success_status)) {
            $status_success_urls[] = $url;
        } else {
            $status_other[] = $url;
        }

    }

    $working_urls = count($status_success_urls);
    $non_working_urls = count($status_other);
    echo "\033[0m";

    // total urls info
    echo $hyphen_divider;
    echo "total urls in sitemap: " . $working_urls + $non_working_urls . $hyphen_divider;
    echo "non-error urls: $working_urls\n";
    echo "client/server error urls: $non_working_urls\n";

}