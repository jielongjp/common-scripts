<?php

$url = readline("URL: ");

$html = file_get_contents($url);

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

$title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
$description = '';

$metas = $dom->getElementsByTagName('meta');
for ($i = 0; $i < $metas->length; $i++) {
    $meta = $metas->item($i);
    if ($meta->getAttribute('name') == 'description') {
        $description = $meta->getAttribute('content');
        break;
    }
}

echo "Title: " . $title . PHP_EOL;
echo "Description: " . $description . PHP_EOL;

?>
