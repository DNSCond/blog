<?php use function Helpers\htmlspecialchars12;

date_default_timezone_set('UTC');
require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/JWT.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/JSONWT.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/dbc/opendb.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/require/createHead2.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/blog/parsedown-1.8.0/Parsedown.php";
function htmlEncodeMinimal(string $value): string
{
    $html = str_replace('<', '&lt;',
        str_replace('&', '&amp;',
            "$value"));
    return ($html);
}

function createSelectElement(string $name, array $options, null|string|callable|array $select = null): string
{
    $name = htmlspecialchars12($name);
    $result = array("<select name=\"$name\">");
    foreach ($options as $key => $val) {
        $selected = false;
        if (is_string($select)) {
            $selected = $select === "$key";
        } elseif (is_callable($select)) {
            $selected = !!$select("$key", $val);
        } elseif (is_array($select)) {
            $selected = in_array("$key", $select);
        }
        $key = htmlspecialchars12($key);
        $val = htmlspecialchars12($val);
        $selected = $selected ? 'selected' : '';
        $result[] = "<option $selected value=\"$key\">$val</option>";
    }
    return implode('', $result) . '</select>';
}

function createSlug($title): string
{
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9 -]/', '', $slug); // Remove special chars
    $slug = str_replace(' ', '-', $slug); // Replace spaces with hyphens
    return trim($slug, '-');
}

