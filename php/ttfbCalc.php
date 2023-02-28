<?php

$url = readline("URL: ");

$totalTime = 0;

// loop x times and calculate average TTFB
for ($i = 0; $i < 101; $i++) {
    
    $ch = curl_init();

    $curlOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 1
    ];

    curl_setopt_array($ch, $curlOptions);

    curl_exec($ch);
    $ttfb = curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME) - curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME);
    $totalTime += $ttfb;
    curl_close($ch);

    progressBar($i, 100);

}

$averageTTFB = $totalTime / 101;

echo "\n
----------------------------------------
$url
Average TTFB score: " . number_format($averageTTFB, 5) . " seconds
----------------------------------------\n";

// show script progress in terminal
function progressBar($done, $total) {
    $perc = floor(($done / $total) * 100);
    $left = 100 - $perc;
    $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
    fwrite(STDERR, $write);
}
