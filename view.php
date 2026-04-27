<?php use ANTHeader\ANTNavLinkTag;
use function ANTHeader\ANTNavBuzz;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\ANTNavReddcond;
use function ANTHeader\create_head2;
use function HashApi\sha256Base64;
use function HashApi\sha384Base64;
use function HashApi\sha512Base64;
use ANTHeader\ANTNavOption;

require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/helpers.php";
$stmt = ($pdo = getPDO())->prepare('SELECT * FROM antrequest.posts WHERE slug=:slug;');
$stmt->execute([':slug' => $GLOBALS['slug'] ?? $_GET['slug'] ?? '']);
$result = $stmt->fetch();
$user_opts = ['base' => '/blog/'];
$links = array(new ANTNavLinkTag('stylesheet', ['poststyle.css', '/gallery/ddDL-table.css']));
$nav_opts = [
        ANTNavFavicond('https://ANTRequest.nl', 'ANTRequest'),
        ANTNavReddcond('.', 'Blogging Engine', true),
        ANTNavBuzz('mkpost.php', 'MakePost Blogging Engine'),
        new ANTNavOption('accounts.php', '/dollmaker2/icon/endpoint.php?' .
                'bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
                'Hash Table', '#00a6a6', '#00ffff'),
];
if (!$result) {
    http_response_code(404);
    create_head2($title = '404 Post Not Found', $user_opts, $links, $nav_opts); ?>
    <div class=divs>
        <h1>Blog not Found</h1>
    </div><?= "\n<!--RESP404-->";
    exit;
}
require_once 'dataDescriptionList.php';
$ctype = $result['texttype'];
$rawText = $result['text'];
$time = strtotime($result['created_at']);
$datetime = gmdate('Y-m-d\\TH:i:s\\Z', $time);
header("Last-Modified:" . gmdate("D, d M Y H:i:s \\G\\M\\T", $time));
$links[] = new ANTNavLinkTag('canonical', "https://antrequest.nl/blog/posts/{$result['slug']}");
create_head2($title = $result['title'], $user_opts, $links, $nav_opts);
$sans = $ctype === 'markdown' ? 'sans' : 'mono';
echo '<div class=divs>' . dataDescriptionList([
                'Title' => $title, 'Raw-Content-Length' => strlen($rawText),
                'Posted-At' => new HTMLSafeEscaped("<relative-time datetime=$datetime>$datetime</relative-time>" .
                        " (<clock-time datetime=$datetime timezone=local>$datetime</clock-time>)"),
                'Raw-sha256b64' => 'sha256b64-' . sha256Base64($tc = $rawText),
                'Raw-sha384b64' => 'sha384b64-' . sha384Base64($tc),
                'Raw-sha512b64' => 'sha512b64-' . sha512Base64($tc),
        ]) . "</div>\n\n";
echo "<article class=\"divs blogpost $sans\">";
if ($ctype === 'markdown') {
    echo (new Parsedown)->setSafeMode(true)->text($rawText);
} elseif ($ctype === 'plaintext') {
    echo "<pre class=pre-plaintext>" . htmlEncodeMinimal($rawText) . "</pre>";
} else {
    $ctype = htmlEncodeMinimal($ctype);
    echo "<h1>CType Error &lt;$ctype&gt;</h1>";
}
echo '</article>';
