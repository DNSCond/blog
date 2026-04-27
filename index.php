<?php use function ANTHeader\create_head2;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\ANTNavReddcond;
use function ANTHeader\ANTNavBuzz;
use ANTHeader\ANTNavOption;

require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/helpers.php";
$pdo = getPDO();

create_head2($title = 'ANT\'s Blog', ['base' => '/blog/',
], array(), [
    ANTNavFavicond('https://ANTRequest.nl', $title),
    ANTNavReddcond('.', 'Blogging Engine', true),
    ANTNavBuzz('mkpost.php', 'MakePost Blogging Engine'),
    new ANTNavOption('accounts.php', '/dollmaker2/icon/endpoint.php?' .
        'bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
        'Hash Table', '#00a6a6', '#00ffff'),
]);
echo "<div class=divs><h1>$title</h1><h2>Posts</h2>";

$stmt = $pdo->prepare("SELECT slug,title,created_at,texttype FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset;");
$stmt->execute([':limit' => 20, ':offset' => 0]);
echo "<ul>";
while ($next = $stmt->fetch()) {
    $time = strtotime($next['created_at']);
    $title = htmlEncodeMinimal("{$next['title']}");
    $datetime = gmdate('Y-m-d\\TH:i:s\\Z', $time);
    $postedAt = "<relative-time datetime=$datetime>$datetime</relative-time>" .
        " (At <clock-time datetime=$datetime timezone=local>$datetime</clock-time>)";
    echo "<li><a href=\"{$next['slug']}\">$title</a>; Posted $postedAt </li>";
}
echo '</ul>';
echo '</div>';
