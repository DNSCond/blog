<?= header('content-type: text/plain') . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
ob_start(function (string $string): string {// normalize_newlines
    return str_replace("\r", "\n", str_replace("\r\n", "\n", "$string"));
});
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title>ANTRequest\'s RSS Feed</title>';
echo "<generator>ANTRequestRSS/0.0.1</generator><docs>https://www.rssboard.org/rss-specification</docs>\n" ?>
<description>
    ANTRequest's RSS Feed
</description><?= "\n<language>en-us</language><link>http://localhost/blog/rss.xml.php</link>";
require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/helpers.php";
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT slug,title,created_at,texttype,text FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset;");
$stmt->execute([':limit' => 50, ':offset' => 0]);
while ($next = $stmt->fetch()) {
    $time = strtotime($next['created_at']);
    $title = htmlEncodeMinimal("{$next['title']}");
    $datetime = gmdate('Y-m-d\\TH:i:s\\Z', $time);
}
echo "</channel></rss>";
