<?php

class MdParse implements JsonSerializable
{
    private array $register = array();
    private array $parsed = array();

    public function __construct()
    {
    }

    public function registerCustomElement(
        string $name, array $allowListAttributes,
        bool   $allowMDInHtml = true, bool $isBlock = false): void
    {
        if (!preg_match('/^[a-z][a-z\\-0-9]*-[a-z\\-0-9]*$/D', $name))
            throw new InvalidArgumentException('Invalid HTML Name');
        $this->register[$name] = [
            'allowListAttributes' => array_map(fn(string $string): string => "$string", $allowListAttributes),
            'allowMDInHtml' => $allowMDInHtml, 'isBlock' => $isBlock,
        ];
    }

    public function parse(string $md): self
    {
        $parsed = array();
        $this->parsed = array();
        $blocks = preg_split("/\\n\\n+/", preg_replace(
            '/\\r\\n|[\\n\\r]/', "\n", trim($md)));
        foreach ($blocks as $block) {
            switch ($block[0]) {
                /** @noinspection PhpMissingBreakStatementInspection */
                case "#":
                    if (preg_match('/^(#{1,6}) ?(.+)/s', $block, $matched)) {
                        $tagName = 'H' . strlen($matched[1]);
                        $parsed[] = new HTMLMdElement($tagName, array(), $this->parseInline($matched[2]));
                        break;
                    }
                case ">":
                    $parsed[] = new HTMLMdElement('BLOCKQUOTE', array(), $this->parseInline(
                        preg_replace('/^>\\s*/m', "\n", $block)));
                    break;
                /** @noinspection PhpMissingBreakStatementInspection */
                case "<":
                    if (preg_match('/^<([a-zA-Z][A-Z\\-0-9]*-[A-Z\\-0-9]*)[^A-Z\\-0-9]+/',
                        $block, $matches)) {
                        if (array_key_exists($matches[1], $this->register)) {
                            if ($this->register[$matches[1]]['isBlock']) {
                                $parsed[] = new HTMLMdElement($matches[1],
                                    array(),
                                    $this->register[$matches[1]]['allowMDInHtml'] ?
                                        $this->parseInline($block) : $block);
                                break;
                            }
                        }
                    }
                default:
                    $parsed[] = new HTMLMdElement('P', array(),
                        $this->parseInline($block));
                    break;
            }
        }
        $this->parsed = $parsed;
        return $this;
    }

    public function htmlEscape(string $string): string
    {
        return htmlspecialchars($string, ENT_HTML5 | ENT_QUOTES | ENT_SUBSTITUTE);
    }

    public function parseInline(string $string): array
    {
        $imploded = false;
        $implode = array();
        foreach (explode("\x20\x20\n", $string) as $entry) {
            if ($imploded) $implode[] = new HTMLMdElement('BR');
            else$imploded = true;
            $implode[] = new HTMLMdElement('SPAN', array(),
                [$this->htmlEscape($entry)]);
        }
        return $implode;
    }

    public function jsonSerialize(): array
    {
        return $this->parsed;
    }

    public function __toString(): string
    {
        return implode('', $this->parsed);
    }
}

class HTMLMdElement implements JsonSerializable
{
    private string $name;
    private array $attributes;
    private array $children;

    public function __construct(string $name, array $attributes = array(), array $children = array())
    {
        if (!preg_match('/^[a-zA-Z][A-Z\\-0-9]*$/D', $this->name = $name))
            throw new InvalidArgumentException('Invalid HTML Name');
        $this->children = array_map(fn(HTMLMdElement|string $array): HTMLMdElement|string => $array, $children);
        $this->attributes = $attributes;
        $this->name = strtoupper($this->name);
    }

    public function __toString(): string
    {
        $html = "<$this->name";
        foreach ($this->attributes as $key => $attribute) {
            $html = "$html\x20$key=\"$attribute\"";
        }
        $html = "$html>";
        $html .= implode('', array_map(
            fn(HTMLMdElement|string $array): string => (string)$array, $this->children));
        return "$html</$this->name>";
    }

    public function jsonSerialize(): array
    {
        return ['tagName' => $this->name, 'attributes' => $this->attributes, 'children' => $this->children];
    }
}
