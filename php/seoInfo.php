<?php

$url = readline("URL: ");

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$html = curl_exec($ch);
curl_close($ch);

// some pages in Japanese without xml encoding tag gets messed up with text from DOMDocument
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

// title and meta description
$title = $dom->getElementsByTagName('title')->item(0)->textContent;
$metaTags = $dom->getElementsByTagName('meta');
foreach ($metaTags as $tag) {
    if (strtolower($tag->getAttribute('name')) == 'description') {
        $metaDescription = $tag->getAttribute('content');
        break;
    }
}

// meta robots
$metaRobots = [];
foreach ($metaTags as $tag) {
    if (strtolower($tag->getAttribute('name')) == 'robots') {   
        $metaRobots[] = $tag->getAttribute('content');
    }
}


// canonical
$linkTags = $dom->getElementsByTagName('link');
foreach ($linkTags as $tag) {
    if (strtolower($tag->getAttribute('rel')) == 'canonical') {
        $canonicalLink = $tag->getAttribute('href');
    }
}

// content of the first h1 tag
$h1Tags = $dom->getElementsByTagName('h1');
if ($h1Tags->length > 0) {
    $h1Content = $h1Tags->item(0)->textContent;
}

// total number of h tags 
$hTagNums = ["h1","h2","h3","h4","h5","h6"];
$headings = [];

foreach ($hTagNums as $h) {
    $tags = $dom->getElementsByTagName($h);
    if($tags) {
        array_push($headings, $tags->length);
    }
}
$numHTags = array_sum($headings);


// number of links on the page (excl fragment links and javascript:void links)
$linkTags = $dom->getElementsByTagName('a');
$numLinks = 0;
foreach ($linkTags as $tag) {
    $href = $tag->getAttribute('href');
    if (strpos($href, '#') !== 0 && strpos($href, 'javascript:void(') !== 0) {
        $numLinks++;
    }
}

// number of images with alt text
$imgTags = $dom->getElementsByTagName('img');
$numImgs = $imgTags->length;
$numImgsWithAlt = 0;
foreach ($imgTags as $tag) {
    if ($tag->hasAttribute('alt')) {
        $numImgsWithAlt++;
    }
}

// HTML lang tag (if used)
$htmlTag = $dom->getElementsByTagName('html')->item(0);
if ($htmlTag->hasAttribute('lang')) {
    $htmlLang = $htmlTag->getAttribute('lang');
}

// printing
echo "\033[36m"; // blue
if (isset($title)) {
    echo "Title: $title\n";
}
if (isset($metaDescription)) {
    echo "Meta description: $metaDescription\n";
}
if (!empty($metaRobots)) {
    echo "Meta robots: ";
    foreach($metaRobots as $robots) {
        echo "$robots ";
    }
    echo "\n";
}
if (isset($canonicalLink)) {
    echo "Canonical: $canonicalLink\n";
}
if (isset($h1Content)) {
    echo "First h1: $h1Content\n";
}
echo "h tag count: $numHTags\n";
echo "Link count: $numLinks\n";
echo "Images with alt text / total images: $numImgsWithAlt / $numImgs\n";
if (isset($htmlLang)) {
    echo "HTML lang: $htmlLang\n";
}
echo "\033[0m";
