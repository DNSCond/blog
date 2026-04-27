<?php use function ANTHeader\ANTNavBuzz;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\ANTNavReddcond;
use function ANTHeader\create_head2;
use function HashApi\sha256Base64;
use function HashApi\sha384Base64;
use function HashApi\sha512Base64;
use ANTHeader\ANTNavLinkTag;
use ANTHeader\ANTNavOption;
use function Helpers\htmlspecialchars12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/helpers.php";
$links = array(new ANTNavLinkTag('stylesheet', ['poststyle.css', '/gallery/ddDL-table.css']));
$pdo = getPDO();
$verifiedAs = false;
$tcExists = array_key_exists('textcontent', $_POST);
$rawText = $tc = $tcExists ? "{$_POST['textcontent']}" : '';
$titleExists = array_key_exists('title', $_POST);
$title = $titleExists ? "{$_POST['title']}" : '';
$ctype = array_key_exists('ctype', $_POST) ? match ("{$_POST['ctype']}") {
    '', 'markdown' => 'markdown',
    default => 'plaintext',
} : 'markdown';
if (array_key_exists('sessid', $_COOKIE)) {
    $jsonwt = getJSONWT();
    if ($data = $jsonwt->validate("{$_COOKIE['sessid']}")) {
        $links[] = new ANTNavLinkTag('stylesheet', 'accountbanner.css');
        $verifiedAs = htmlEncodeMinimal("{$data['autoauthOf']}");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['action'] === 'post' && $tcExists) {
                $stmt = $pdo->prepare('SELECT id FROM antrequest.accounts WHERE username=:username;');
                $stmt->execute([':username' => "{$data['autoauthOf']}"]);
                if ($author = $stmt->fetch()) {
                    $stmt = $pdo->prepare('INSERT INTO antrequest.posts(author, text, created_at, texttype, slug, title)'
                            . ' VALUES (:author, :text, :created_at, :texttype, :slug, :title);');
                    $stmt->execute([':author' => $author['id'], ':text' => $rawText, ':title' => $title,
                            ':created_at' => gmdate('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
                            ':slug' => ($slug = createSlug($title)), ':texttype' => $ctype,
                    ]);
                    http_response_code(201);
                    header("Location: posts/$slug");
                    $GLOBALS['referer'] = 'mkpost-create';
                    $GLOBALS['view'] = $slug;
                    require_once 'view.php';
                    exit;
                }
            }
        }
    }
}

require_once 'dataDescriptionList.php';
create_head2($htmlpageTitle = 'Make a post', array(), $links, [
        ANTNavFavicond('https://ANTRequest.nl', $htmlpageTitle),
        ANTNavReddcond('/blog/', 'Blogging Engine'),
        ANTNavBuzz('/blog/mkpost.php', 'MakePost Blogging Engine', true),
        new ANTNavOption('/blog/accounts.php', '/dollmaker2/icon/endpoint.php?' .
                'bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
                'Hash Table', '#00a6a6', '#00ffff'),
]) ?>
<div class=accountbanner><?= $verifiedAs ? "<div><div>$verifiedAs</div></div>" : '' ?></div>
<div class=divs>
    <h1>Make a Post</h1>
    <form method=post><h2>Make a Post</h2>
        <label for=title>Title:</label>
        <div>
            <input id=title name=title value="<?= htmlspecialchars12($title) ?>" style=width:100%>
        </div>
        <label for=textarea>Text:</label>
        <div>
            <textarea id=textarea name=textcontent style=width:100%;box-sizing:border-box rows=30
                      data-hash="<?= $tcExists ? ('sha256b64-' . sha256Base64($tc)) : '' ?>"
            ><?= $tcExists ? htmlEncodeMinimal($tc) : '' ?></textarea>
        </div>
        <label><?= 'Choose content type: ' . createSelectElement('ctype',
                    ['markdown' => 'text/markdown', 'plaintext' => 'text/plain'],
                    "{$_POST['ctype']}") ?></label>
        <button type=submit name=action value=preview>Preview</button>
        <div><?= ($tcExists ? '<h2>Preview Post</h2>' : '');
            if ($tcExists) {
                $sans = $ctype === 'markdown' ? 'sans' : 'mono';
                echo "<article class=\"blogpost $sans left-border\">";
                if ($ctype === 'markdown') {
                    $parsedown = (new Parsedown)->setSafeMode(true);
                    echo /*$html=*/
                    $parsedown->text($rawText);
                } elseif ($ctype === 'plaintext') {
                    echo "<pre class=pre-plaintext>" . htmlEncodeMinimal($rawText) . "</pre>";
                }
                echo '</article>' . dataDescriptionList([
                                'Raw-Content-Length' => strlen($rawText),
                                'sha256b64' => 'sha256b64-' . sha256Base64($tc),
                                'sha384b64' => 'sha384b64-' . sha384Base64($tc),
                                'sha512b64' => 'sha512b64-' . sha512Base64($tc),
                        ]);
                echo '<button type=submit name=action value=post>Submit</button>';
            } ?></div>
    </form>
</div>