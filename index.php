<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

function debug($label, $value) {
    echo("<p>$label<br /><pre>".htmlentities(print_r($value, 1))."</pre></p>");
}

define('BLOG_CONFIG_PATH', 'config.json');
define('BLOG_HTTP_URL', sprintf('http://%s%s', $_SERVER['SERVER_NAME'], pathinfo($_SERVER['REQUEST_URI'], PATHINFO_DIRNAME)));

if (is_file('config.json')) {
    $config = json_decode(file_get_contents(BLOG_CONFIG_PATH), true);
} elseif(is_file('install.php')) {
    header('Location: '.pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME).'/'.'install.php');
}

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
$now = time();
// debug('list', $list);
// debug('content', $content);
?>
<html>
<head>
<title>xox blog</title>
<link rel="alternate" type="application/rss+xml" title="Graphicslab" href="data/blog.rss" />
</head>
<body>
<h1><?= $config['title'] ?></h1>
<?php if (!empty($content)) : ?>
<?php foreach($list as $key => $value) : ?>
<?php if (array_key_exists($key, $content) && ($value != '') && ($value <= $now)) : ?>
<?= file_get_contents(BLOG_CACHE_PATH.$content[$key]['html_name']); ?>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; // content ?>
</body>
</html>
