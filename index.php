<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

function debug($label, $value) {
    echo("<p>$label<br /><pre>".htmlentities(print_r($value, 1))."</pre></p>");
}
// phpinfo();
// debug('server', $_SERVER);

define('BLOG_CONFIG_PATH', 'config.json');
define('BLOG_HTTP_URL', sprintf('http://%s%s', $_SERVER['SERVER_NAME'], pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME)));

if (is_file('config.json')) {
    $config = json_decode(file_get_contents(BLOG_CONFIG_PATH), true);
} elseif(is_file('install.php')) {
    header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME).'/'.'install.php');
}

$config['stylesheet_css'] = 'view/impagina_blog.css';

// debug('config', $config);

define('BLOG_CONTENT_PATH', $config['data_path'].'content.json');
define('BLOG_LIST_PATH', $config['data_path'].'list.json');
define('BLOG_CACHE_PATH', $config['data_path'].'cache/');
if (is_file(BLOG_CONTENT_PATH) && is_file(BLOG_LIST_PATH)) {
    $content = json_decode(file_get_contents(BLOG_CONTENT_PATH), true);
    $list = json_decode(file_get_contents(BLOG_LIST_PATH), true);
} elseif(is_file('install.php')) {
    header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME).'/'.'install.php');
}

// define('BLOG_MODREWRITE_ENABLED', array_key_exists('HTTP_MOD_REWRITE', $_SERVER));
define('BLOG_MODREWRITE_ENABLED', true);

define('BLOG_TEMPLATE_HEADER_PATH', 'view/template_header.html');
define('BLOG_TEMPLATE_ARTICLE_PATH', 'view/template_article.html');
define('BLOG_TEMPLATE_FOOTER_PATH', 'view/template_footer.html');

if ((BLOG_TEMPLATE_HEADER_PATH != '') && file_exists(BLOG_TEMPLATE_HEADER_PATH)) {
    define('BLOG_TEMPLATE_HEADER', file_get_contents(BLOG_TEMPLATE_HEADER_PATH));
} else {
    define('BLOG_TEMPLATE_HEADER', <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>\$title</title>
<link rel="alternate" type="application/rss+xml" title="\$title" href="\$blog_http_url/data/blog.rss" />
<link href="view/\$stylesheet_css" rel="stylesheet" type="text/css" media="screen">
<style>
</style>
</head>
<body>
<header>
</header>     
<h1><a href="\$blog_http_url">\$title</a></h1>
EOT
    );
}
if ((BLOG_TEMPLATE_ARTICLE_PATH != '') && file_exists(BLOG_TEMPLATE_ARTICLE_PATH)) {
    define('BLOG_TEMPLATE_ARTICLE', file_get_contents(BLOG_TEMPLATE_ARTICLE_PATH));
} else {
    define('BLOG_TEMPLATE_ARTICLE', <<<EOT
<article>
\$content
</article>
EOT
    );
}
if ((BLOG_TEMPLATE_FOOTER_PATH != '') && file_exists(BLOG_TEMPLATE_FOOTER_PATH)) {
    define('BLOG_TEMPLATE_FOOTER', file_get_contents(BLOG_TEMPLATE_FOOTER_PATH));
} else {
    define('BLOG_TEMPLATE_FOOTER', <<<EOT
</body>
</html>
EOT
    );
}

echo(strtr(
    BLOG_TEMPLATE_HEADER,
    array (
        '$title' => $config['title'],
        '$blog_http_url' => BLOG_HTTP_URL,
        // TODO: $site_http_url?
    )
));

// debug('_REQUEST', $_REQUEST);
// debug('list', $list);
// debug('content', $content);
$now = time();
if (is_array($content) && !empty($content)) {
    if (array_key_exists('a', $_REQUEST) && array_key_exists($_REQUEST['a'], $content)) {
        $article = $_REQUEST['a'];
    }
    if (isset($article)) {
        if (($list[$article] != '') && ($list[$article] <= $now)) {
            echo(strtr(
                BLOG_TEMPLATE_ARTICLE,
                array (
                    '$content' => file_get_contents(BLOG_CACHE_PATH.$content[$article]['html_name']),
                )
            ));
        }
    } else {
        foreach($list as $key => $value) {
            if (array_key_exists($key, $content) && ($value != '') && ($value <= $now)) {
                echo(strtr(
                    BLOG_TEMPLATE_ARTICLE,
                    array (
                        '$content' => file_get_contents(BLOG_CACHE_PATH.$content[$key]['html_name']),
                    )
                ));
            }
        }
    }
}
echo(BLOG_TEMPLATE_FOOTER);
