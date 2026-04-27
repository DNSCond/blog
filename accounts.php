<?php use ANTHeader\ANTNavLinkTag;
use function ANTHeader\create_head2;
use function ANTHeader\ANTNavFavicond;
use function ANTHeader\ANTNavReddcond;
use function ANTHeader\ANTNavBuzz;
use function ANTHeader\set_cookie;
use ANTHeader\ANTNavOption;

require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/helpers.php";
$pdo = getPDO();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('username', $_POST) && is_string($_POST['username']) &&
            array_key_exists('password', $_POST) && is_string($_POST['password'])) {
        $stmt = $pdo->prepare('SELECT id,password FROM antrequest.accounts WHERE username=:username;');
        $stmt->execute([':username' => $_POST['username']]);
        if ($result = $stmt->fetch()) {
            if (password_verify("{$_POST['password']}", $result['password'])) {
                $jsonwt = getJSONWT();
                $stmt = $pdo->prepare('UPDATE antrequest.accounts SET automatedAuth=:automatedAuth WHERE id=:id');
                $automatedAuth = $jsonwt->generate(['autoauthOf' => $_POST['username']]);
                $stmt->execute([':automatedAuth' => $automatedAuth,':id' => $result['id']]);
                set_cookie('sessid', $automatedAuth, array('HttpOnly' => true, 'max-age' => 3600));
                http_response_code(303);
                header('Location: /blog/');
            }
        }
    }
}
create_head2($title = 'Accounts V7', ['base' => '/blog/',
], array(new ANTNavLinkTag('stylesheet', 'visual-table.css')), [
        ANTNavFavicond('https://ANTRequest.nl', $title),
        ANTNavReddcond('/blog/', 'Blogging Engine'),
        ANTNavBuzz('/blog/mkpost.php', 'MakePost Blogging Engine'),
        new ANTNavOption('/blog/accounts.php', '/dollmaker2/icon/endpoint.php?' .
                'bgcolor=%2300a8f3&fgcolor=%238cfffa&L=%23fff200&W=%23000000&LC=%23ff0000&RC=%230000ff&v=1',
                'Hash Table', '#00a6a6', '#00ffff', true),
]); ?>
<div class=divs><h1 style=text-align:center>Login Form</h1>
    <form method=post class=login-form>
        <label for=username>Username:</label>
        <input id=username name=username autocomplete=username>
        <label for=password>Password:</label>
        <input id=password name=password type=password autocomplete=current-password>
        <button type=submit>LogIn</button>
    </form>
</div>
