<?= '<!DOCTYPE html><meta charset=UTF-8><style>body{font-family:monospace}</style>';
/** @noinspection HtmlUnknownTarget */
echo '<script type=module src=/gallery/JSONScript.js></script><body>';
require_once 'mdparser.php';
$mdParser = new MdParse;
echo "\n<script type=application/json is=output-script>" . json_encode($mdParser->parse("##hello\n\nln1  \nhelloo\n
> line3  
line4

< line5>

<div></div>

<relative-time datetime='2024'></relative-time>


######################## hello"), JSON_INVALID_UTF8_SUBSTITUTE) . "</script>\n"; ?>
    <script type=module>
        const pre = document.createElement('pre'),
            code = document.createElement('code');
        code.textContent = JSON.stringify(document.querySelector(
            "script[type='application/json'][is='output-script']"), null, 2);
        pre.append(code);
        document.body.append(pre);
    </script><?= $mdParser;
