<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

define('BLOG_CONFIG_PATH', 'data/config.php');
define('BLOG_CONTENT_PATH', 'data/content.json');
define('BLOG_LIST_PATH', 'data/list.json');
define('BLOG_CACHE_PATH', 'data/cache/');

$error = array();
if (!is_dir(BLOG_CACHE_PATH)) {
    if (!@mkdir(BLOG_CACHE_PATH, 777)) {
        $error[] = "the ".BLOG_CACHE_PATH." directory does not exist and could not be created";
    }
}
foreach (array(BLOG_CONTENT_PATH, BLOG_LIST_PATH, BLOG_CONFIG_PATH) as $item) {
    if (!is_file($item)) {
        if (!@touch($item)) {
            $error[] = "the ".$item." file does not exist and could not be created";
        }
    } else if (!is_writable($item)) {
        $error[] = "the ".$item." file is not writable";
    }
}

if (!empty($error)) {
    foreach ($error as $item) {
        echo("<p>".$item."</p>\n");
    }
    die();
}


// phpinfo();
function debug($label, $value) {
    echo("<p>$label<br /><pre>".htmlentities(print_r($value, 1))."</pre></p>");
}
function get_content_from_github($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // curl_setopt($ch, CURLOPT_HEADER, true);
    // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    // curl_setopt($ch, CURLOPT_VERBOSE, true);
    $content = curl_exec($ch);
    // debug('curl getinfo', curl_getinfo($ch));
    curl_close($ch);
    return $content;
}

$url = "https://api.github.com/rate_limit";
$rate_limit = json_decode(get_content_from_github($url));
// debug('rate_limit', $rate_limit);

echo("<p>".$rate_limit->rate->remaining." hits remaining of ".$rate_limit->rate->limit.".</p>");

$url= "https://api.github.com/repos/aoloe/htdocs-xox/contents/"; // gets the list of files in the directory
$url= "https://api.github.com/repos/aoloe/htdocs-blog-xox/contents/text"; // gets the list of files in the directory
// GET /repos/:owner/:repo/contents/:path
if (false) { // @XXX: only for debugging purpose: if false don't ping each time github
    $content_github = get_content_from_github($url);
    // debug('content', $content);
    file_put_contents("content_github.json", $content_github);
} else {
    $content_github = file_get_contents("content_github.json");
}
$content_github = json_decode($content_github);
// debug('content_github', $content_github);
$content = array();
if (file_exists(BLOG_CONTENT_PATH)) {
    $content = file_get_contents(BLOG_CONTENT_PATH);
    $content = json_decode($content);
}
if (!is_array($content)) {
    $content = array();
}
// debug('content', $content);
include_once "spyc.php";
include_once "markdown.php";

if (is_array($content_github)) {

    $changed = false;
    foreach ($content_github as $item) {
        debug('item', $item);
        if ($item->type == 'file') {
            if (!array_key_exists($item->path, $content)) {
                $content[$item->name] = array (
                    'path' => $item->path,
                    'name' => $item->name,
                    'html_name' => pathinfo($item->name, PATHINFO_FILENAME).'.html',
                    'raw_url' => 'https://raw.github.com/aoloe/htdocs-blog-xox/master/'.$item->path,
                    'sha' => '',

                    'author' => '',
                    'title' => '',
                    'tags' => '',
                );
            }
            $content_item = $content[$item->name];
            if ($item->sha != $content_item['sha']) {
                $changed = true;
                debug('item', $item);
                $file = get_content_from_github($content_item['raw_url']);
                debug('file', $file);
                $yaml_end = 0;
                if ((substr($file, 0, 3) == '---') && ($yaml_end = strpos($file, '---', 4))) {
                    debug('yaml_end', $yaml_end);
                    $metadata = Spyc::YAMLLoadString(substr($file, 4, $yaml_end));
                    debug('metadata', $metadata);
                    $content_item['date'] = (array_key_exists('date', $metadata) ? $metadata['date'] : date('d.m.Y'));
                    if (array_key_exists('author', $metadata)) {
                        $content_item['author'] = $metadata['author'];
                    }
                    if (array_key_exists('title', $metadata)) {
                        $content_item['title'] = $metadata['title'];
                    }
                    $yaml_end += 4; // remove the closing ---
                }
                if ($yaml_end > 0) {
                    $file = substr($file, $yaml_end);
                }
                debug('file', $file);
                $header = array();
                if ($content_item['title'] != '') {
                    $header[] = '<h2 class="blog">'.$content_item['title'].'</h2>';
                }
                $header[] = sprintf(
                    '<p class="blog_author_date">%s%s</p>', 
                    $content_item['author'] == '' ? '' : $content_item['author'].', ',
                    $content_item['date']
                );
                if ($content_item['tags'] != '') {
                    $header[] = '<p class="blog_tags">'.$content_item['tags'].'</p>';
                }
                $file = implode("\n", $header)."\n".Markdown($file);
                file_put_contents(BLOG_CACHE_PATH.$item->name, $file);
                $content_item['sha'] = $item->sha;
                $content[$item->name] = $content_item;
                debug('content_item', $content_item);
            } // if sha != sha
        } // if file
    } // foreach content_github
    if ($changed) {
        $list = array();
        foreach ($content as $key => $value) {
            $list[BLOG_CACHE_PATH.$value['html_name']] = strtotime($value['date']);
        }
        asort($list);
        debug('list', $list);
    }
} // if is_array

if (is_writable(BLOG_CONTENT_PATH)) {
    //file_put_contents(BLOG_CONTENT_PATH, json_encode($content));
} else {
    echo("<p>Could not store content.json</p>");
}
