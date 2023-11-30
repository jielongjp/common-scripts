<?php

$url = readline('URL: ');

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');

$response = curl_exec($ch);

  $parseUrl = parse_url($url);
  // echo var_dump($parseUrl);
  $domain = $parseUrl['scheme'] . '://' . $parseUrl['host'];
    echo $domain;

    preg_match_all('/href=[\"\']?([^\s\"\'>]+)[\"\']?/', $response, $matches);
  $links = array_unique($matches[1]);
  echo var_dump($links);

  foreach ($links as $link) {
    // skip javascript:void links etc
    if (strpos($link, 'javascript:void') === false && (strpos($link, '#') === 0) === false) {
        
        // add domain if link starts without http url (if relative path)
        if (!str_starts_with($link, 'https://') && !str_starts_with($link, 'http://')) {
            if (str_starts_with($link, '/') && str_ends_with($domain, '/')) {
                $link = rtrim($domain, "/") . $link;
            }
             else  {
                $link = $domain . $link;
            }
        }
        

        usleep(5000);

        $ch_link = curl_init($link);

        curl_setopt($ch_link, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_link, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_link, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');

        curl_exec($ch_link);
        $statusCode = curl_getinfo($ch_link, CURLINFO_HTTP_CODE);

        // print different colors for http status
        if ($statusCode === 200) {
            echo "\033[0m$link - $statusCode\n";
        } elseif (strpos($statusCode, '4') === 0) {
            echo "\033[31m$link - $statusCode\n";
        } elseif (strpos($statusCode, '5') === 0) {
            echo "\033[33m$link - $statusCode\n";
        } else {
            echo "\033[36m$link - $statusCode\n";
        }

        curl_close($ch_link);
    }
  }

curl_close($ch);