<?php // CustomParsedown

class CustomParsedown extends Parsedown
{
    private string $tags = 'func-def|rest-def|text-script';

    function __construct()
    {
        // Tell Parsedown to trigger our function whenever it sees '@' inline
        $this->InlineTypes['@'][] = 'Timestamp';
        // Tell Parsedown to check '@' before checking other default inline characters
        $this->inlineMarkerList .= '@';
        //---
        // 2. Register '<' for custom tags
        $this->InlineTypes['<'][] = 'CustomTag';
        $this->inlineMarkerList .= '<';
    }

    protected function inlineTimestamp($excerpt): ?array
    {
        // Your regex, adapted to match from the start of the current position in the text
        $pattern = '/^@(?:([RTDXUtdjJ]):)?(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[-+]\d{2}:\d{2}))/i';

        if (preg_match($pattern, $excerpt['text'], $matches)) {
            $flag = !empty($matches[1]) ? strtoupper($matches[1]) : 'DEFAULT';
            $cleanIsoString = $matches[2];

            // Parsedown expects an array mapping out the HTML structure safely
            return [
                'extent' => strlen($matches[0]), // Tells Parsedown how many characters to skip over
                'element' => [
                    'name' => 'time',
                    'text' => $cleanIsoString, // Temporary raw text fallback
                    'attributes' => [
                        'datetime' => $cleanIsoString,
                        'data-flag' => $flag,
                    ],
                ],
            ];
        }
        return null;
    }

    /**
     * Handles custom tags like <func-def>, <rest-def>, and <text-script>
     */
    protected function inlineCustomTag($excerpt): ?array
    {
        // Regex to find open tags we care about: <(func-def|rest-def|text-script) attributes>
        // It captures the tag name in group 1, and the raw attributes string in group 2
        $pattern = '/^<(' . $this->tags . ')\b([^>]*)\s*>/i';

        if (preg_match($pattern, $excerpt['text'], $matches)) {
            $tagName = strtolower($matches[1]);
            $rawAttributes = $matches[2];

            // Parse the key="value" attributes into a clean PHP array
            $attributes = $this->parseAttributes($rawAttributes);

            // Whitelist filtering (Security: removes unauthorized class, id, or onclick)
            $safeAttributes = $this->filterWhitelistedAttributes($tagName, $attributes);

            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => $tagName,
                    'attributes' => $safeAttributes,
                    'handler' => 'elements',
                    'text' => [
                        // 1. Inject the declarative shadow root template as the first child
                        [
                            'name' => 'template',
                            'attributes' => ['shadowrootmode' => 'open'],
                            'handler' => 'elements',
                            'text' => [
                                [
                                    'name' => 'div',
                                    'attributes' => ['class' => 'signature-box'],
                                    'text' => "function {$safeAttributes['name']}()",
                                ],
                                [
                                    'name' => 'slot', // The slot element inside the shadow root
                                    'text' => '',
                                ]
                            ]
                        ],
                        // 2. Parsedown will continue to append the rest of the inner markdown
                        // text as standard light DOM children after this template!
                    ],
                ],
            ];
        }

        return null; // Return null to let standard HTML tags (like <a> or <div>) pass through normally
    }

    /**
     * Helper to parse raw attribute strings (e.g., 'name="calc" method="POST"') into arrays
     */
    private function parseAttributes(string $rawAttributes): array
    {
        $attributes = [];
        // Matches key="value" or key='value' pairs
        preg_match_all('/([a-zA-Z0-9-]+)\s*=\s*["\']([^"\']*)["\']/',
            $rawAttributes, $attrMatches, PREG_SET_ORDER);

        foreach ($attrMatches as $match) {
            $attributes[strtolower($match[1])] = $match[2];
        }
        return $attributes;
    }

    /**
     * Security check: Only allows safe, intended configuration keys
     */
    private function filterWhitelistedAttributes(string $tagName, array $attributes): array
    {
        $whitelist = [
            'func-def' => ['name', 'arguments', 'return'],
            'rest-def' => ['method', 'path', 'auth'],
            'text-script' => ['type']
        ];

        $allowedKeys = $whitelist[$tagName] ?? [];
        $cleanAttributes = array();
        foreach ($allowedKeys as $key) {
            if (isset($attributes[$key])) {
                // Escape values to prevent XSS injection injection inside HTML boundaries
                $cleanAttributes[$key] = htmlspecialchars($attributes[$key],
                    ENT_HTML5 | ENT_QUOTES, 'UTF-8');
            }
        }
        return $cleanAttributes;
    }

    public function text($text): string
    {
        $html = parent::text($text);
        return preg_replace('/<p>\\s*(<(' . $this->tags . ')[^>]*>.*?<\\/\\2>)\s*<\\/p>/is', '$1', $html);
    }
}

function createParsedown(): CustomParsedown
{
    return (new CustomParsedown)->setSafeMode(true);
}
