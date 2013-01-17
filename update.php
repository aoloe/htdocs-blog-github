<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

function debug($label, $value) {
    echo("<p>$label<br /><pre>".htmlentities(print_r($value, 1))."</pre></p>");
}

define('BLOG_CONFIG_PATH', 'config.json');
define('BLOG_HTTP_URL', sprintf('http://%s%s', $_SERVER['SERVER_NAME'], pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME)));
// define('BLOG_MODREWRITE_ENABLED', array_key_exists('HTTP_MOD_REWRITE', $_SERVER));
define('BLOG_MODREWRITE_ENABLED', true);
define('BLOG_GITHUB_NOREQUEST', false); // for debugging purposes only
define('BLOG_FORCE_UPDATE', false); // for debugging purposes only
define('BLOG_STORE_NOUPDATE', false); // for debugging purposes only

// debug('apache get_env', apache_getenv('HTTP_MOD_REWRITE'));

if (is_file(BLOG_CONFIG_PATH)) {
    $config = json_decode(file_get_contents(BLOG_CONFIG_PATH), 1);
} else {
    header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME).'/'.'install.php');
}
?>
<html>
<head>
<title><?= $config['title'] ?></title>
<style>
    .warning {background-color:yellow;}
</style>
</head>
<body>
<h1><?= $config['title'] ?> Update</h1>
<?php
if (file_exists('install.php')) {
    echo('<p class="warning">You should remove the <a href="install.php">install file</a>.</p>');
}

define('BLOG_CONTENT_PATH', $config['data_path'].'content.json');
define('BLOG_LIST_PATH', $config['data_path'].'list.json');
define('BLOG_RSS_PATH', $config['data_path'].'blog.rss');
define('BLOG_CACHE_PATH', $config['data_path'].'cache/');
define('BLOG_RSS_ITEMS_NUMBER', 10);
define('BLOG_TEMPLATE_ITEM_PATH', 'view/template_item.html');

define('BLOG_TEMPLATE_RSS_HEAD', <<<EOT
<?xml version="1.0"?>
<rss version="2.0"  xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title>\$title</title>
<link>\$url</link>
<description>\$description</description>
<language>en-us</language>
<pubDate>\$now</pubDate>
<lastBuildDate>\$now</lastBuildDate>
<managingEditor>\$contact</managingEditor>
<webMaster>\$contact</webMaster>
EOT
);
define('BLOG_TEMPLATE_RSS_FOOTER', <<<EOT
</channel>
</rss>
EOT
);
define('BLOG_TEMPLATE_RSS_ITEM', <<<EOT
    <item>
        <author>\$author</author>
        <link>\$link</link>
        <title>\$title</title>
        <category>![CDATA[\$category]]</category>
        <pubDate>\$date</pubDate>
        <description><![CDATA[\$content]]></description>
    </item>
EOT
);
if ((BLOG_TEMPLATE_ITEM_PATH != '') && file_exists(BLOG_TEMPLATE_ITEM_PATH)) {
    define('BLOG_TEMPLATE_ITEM', file_get_contents(BLOG_TEMPLATE_ITEM_PATH));
} else {
    define('BLOG_TEMPLATE_ITEM', <<<EOT
<header>
<h2 class="blog" id="\$id\"><a href="\$url">\$title</a></h2>
<p class="blog_author_date">\$author, \$date</p>
<p class="blog_tags">\$tags</p>
</header>
\$content
EOT
    );
}

// debug('config', $config);

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

$rate_limit = json_decode(get_content_from_github("https://api.github.com/rate_limit"));
// debug('rate_limit', $rate_limit);

echo("<p>".$rate_limit->rate->remaining." hits remaining out of ".$rate_limit->rate->limit." for the next hour.</p>");

if (!BLOG_GITHUB_NOREQUEST) {
    $content_github = get_content_from_github($config['github_url']);
    file_put_contents("content_github.json", $content_github);
} else {
    echo('<p class="warning">Requests are from the cache: queries to GitHub are disabled.</p>');
    $content_github = file_get_contents("content_github.json");
}
$content_github = json_decode($content_github);
// debug('content_github', $content_github);
if (!is_array($content_github) && property_exists($content_github, 'message') && ($content_github->message == 'Not Found')) {
    echo("<p>".$config['github_url']." not found.</p>\n");
    die();
}
$content = array();
if (!array_key_exists('force', $_REQUEST) && !BLOG_FORCE_UPDATE) {
    if (file_exists(BLOG_CONTENT_PATH)) {
        $content = file_get_contents(BLOG_CONTENT_PATH);
        $content = json_decode($content, 1);
    }
    if (!is_array($content)) {
        $content = array();
    }
}
// debug('content', $content);
// debug('content_github', $content_github);
include_once "spyc.php";
include_once "markdown.php";

if (is_array($content_github)) {

    $changed = 0;
    foreach ($content_github as $item) {
        // debug('item', $item);
        if ($item->type == 'file') {
            $id = pathinfo($item->name, PATHINFO_FILENAME);
            if (!array_key_exists($id, $content)) {
                $content[$id] = array (
                    'path' => $item->path,
                    'name' => $item->name,
                    'html_name' => $id.'.html',
                    'id' => $id,
                    'raw_url' => $config['github_url_raw'].$item->path,
                    'sha' => '',

                    'date' => '',
                    'author' => '',
                    'title' => '',
                    'tags' => '',
                );
            }
            $content_item = $content[$id];
            // debug('content_item', $content_item);
            if ($item->sha != $content_item['sha']) {
                $changed++;
                // debug('item', $item);
                $file = get_content_from_github($content_item['raw_url']);
                // debug('file', $file);
                $yaml_end = 0;
                if ((substr($file, 0, 3) == '---') && ($yaml_end = strpos($file, '---', 4))) {
                    // debug('yaml_end', $yaml_end);
                    $metadata = Spyc::YAMLLoadString(substr($file, 4, $yaml_end));
                    // debug('metadata', $metadata);
                    if (array_key_exists('date', $metadata)) {
                        $content_item['date'] = $metadata['date'];
                    }
                    $content_item['author'] = (array_key_exists('author', $metadata) ? $metadata['author'] : $config['author']);
                    if (array_key_exists('title', $metadata)) {
                        $content_item['title'] = $metadata['title'];
                    }
                    $yaml_end += 4; // remove the closing ---
                }
                if ($yaml_end > 0) {
                    $file = substr($file, $yaml_end);
                }
                // debug('file', $file);

                $file = strtr(
                    BLOG_TEMPLATE_ITEM,
                    array (
                        '$title' => $content_item['title'],
                        '$id' => $content_item['id'],
                        '$url' => (BLOG_MODREWRITE_ENABLED ? '' : '?a=').$content_item['id'],
                        '$author' => $content_item['author'] == '' ? $config['author'] : $content_item['author'],
                        
                        '$date' => $content_item['date'],
                        '$tags' => $content_item['tags'],
                        '$content' => Markdown($file),
                    )
                );

                file_put_contents(BLOG_CACHE_PATH.$content_item['html_name'], $file);
                $rss_item[] = $file;
                $content_item['sha'] = $item->sha;
                $content[$id] = $content_item;
                // debug('content_item', $content_item);
            } // if sha != sha
        } // if file
    } // foreach content_github
    // debug('content', $content);
    if (!BLOG_STORE_NOUPDATE && ($changed > 0)) {
        $list = array();
        foreach ($content as $key => $value) {
            $list[$value['id']] = strtotime($value['date']);
        }
        arsort($list);
        // debug('list', $list);
        echo("<p>".$changed." new article".($changed > 1 ? 's' : '')." retrieved from ".$config['github_url'].".</p>\n");

        if ((file_exists(BLOG_CONTENT_PATH) && is_writable(BLOG_CONTENT_PATH)) || is_writable($config['data_path'])) {
            // echo('<p class="warning">Storing the content array is disabled.</p>');
            file_put_contents(BLOG_CONTENT_PATH, json_encode($content));
        } else {
            echo("<p>Could not store content.json</p>");
        }
        if (is_writable(BLOG_LIST_PATH)) {
            file_put_contents(BLOG_LIST_PATH, json_encode($list));
        } else {
            echo('<p class="warning">Could not store list.json</p>');
        }

        /**
         * Store the latest ten entries in the rss file
         */
        // debug('content', $content);
        // debug('list', $list);
        file_put_contents(BLOG_RSS_PATH,
            strtr(
                BLOG_TEMPLATE_RSS_HEAD,
                array (
                    '$url' => BLOG_HTTP_URL,
                    '$title' => $config['title'],
                    '$description' => "",
                    '$contact' => $config['contact'],
                    '$now' => strftime("%a, %d %b %Y %H:%M:%S GMT"), // %Z does not work ...
                )
            )
        );
        $n = max(count($list), BLOG_RSS_ITEMS_NUMBER);
        $i = 0;
        foreach ($list as $key => $value) {
            $content_item = $content[$key];
            if (($content_item['date'] == '') || (strtotime($content_item['date']) > time()))
                continue;
            // debug('content_item', $content_item);
            if ($i++ > $n) break;
            // debug('content_item', $content_item);
            // debug('html file', BLOG_CACHE_PATH.$content_item['html_name']);
            // debug('BLOG_HTTP_URL', BLOG_HTTP_URL);
            $rss_content = file_get_contents(BLOG_CACHE_PATH.$content_item['html_name']);
            // converting the relative urls for images and href to absolute ones
            $rss_content = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1'.BLOG_HTTP_URL.'/$2$3', $rss_content);
            $rss_content = preg_replace("#(<\s*img\s+[^>]*src\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1'.BLOG_HTTP_URL.'/$2$3', $rss_content);
            file_put_contents(
                BLOG_RSS_PATH,
                strtr(
                    BLOG_TEMPLATE_RSS_ITEM,
                    array (
                        '$author' => htmlentities($content_item['author']),
                        '$link' => BLOG_HTTP_URL.'/'.(BLOG_MODREWRITE_ENABLED ? '' : '?a=').htmlentities($content_item['id']),
                        '$title' => htmlentities($content_item['title']),
                        '$category' => implode(']]</category><category>![CDATA[', explode(',', $content_item['tags'])),
                        '$date' => strftime("%a, %d %b %Y %H:%M:%S GMT", strtotime($content_item['date'])),
                        '$content' => $rss_content,
                    )
                ),
                FILE_APPEND
            );
        }
        file_put_contents(BLOG_RSS_PATH, BLOG_TEMPLATE_RSS_FOOTER, FILE_APPEND);

    } else {
        echo("<p>No new blog articles in ".$config['github_url'].".</p>\n");
    }
} // if is_array

?>
<form method="post">
<input type="checkbox" name="force" value="yes" id="force_update" /> <label for="force_update">Force</label>
<input type="submit" value="&raquo;" />
</form>
<p>You can now <a href="index.php">view your blog</a>.</p>
</body>
</html>
