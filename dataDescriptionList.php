<?php use function Helpers\htmlspecialchars12;
class HTMLSafeEscaped implements JsonSerializable
{
    private string $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function __toString(): string
    {
        return $this->string;
    }

    public function toString(): string
    {
        return $this->string;
    }

    public function jsonSerialize(): string
    {
        return $this->string;
    }
}

function dataDescriptionList(array $data, array $classes = array(), array $keyToTag = array()): string
{
    $values = array();
    foreach ($data as $key => $value) {
        if (is_bool($value)) $value = $value ? "true" : 'false';
        $dataKey = ($key = $key instanceof HTMLSafeEscaped ? "$key" : htmlspecialchars12($key));
        $value = $value instanceof HTMLSafeEscaped ? "$value" : htmlspecialchars12($value);
        $valueStr = htmlspecialchars12($value);
        if (array_key_exists("$key", $keyToTag)) {
            $keyStr = htmlspecialchars12("$keyToTag[$key]");
            $key = "<a href=\"$keyStr\">$key</a>";
        }
        $values[] = "<div data-key=\"$dataKey\" data-value=\"$valueStr\"><dt>$key</dt> <dd>$value</div>";
    }
    $classes[] = 'descLi';
    $classes = htmlspecialchars12(implode(' ', $classes));
    return "\n<dl class=\"$classes\">\n"
        . implode("\n", $values) . "\n</dl>\n";
}

