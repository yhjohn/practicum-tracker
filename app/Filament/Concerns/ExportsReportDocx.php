<?php

namespace App\Filament\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

trait ExportsReportDocx
{
    /**
     * Font, paragraph style and left indent (twips) used by the HTML to Word
     * converter; set per template by setRichText().
     *
     * @var array{font: string, paragraphStyle: ?string, baseIndent: int}
     */
    protected static array $richStyle = ['font' => 'Times New Roman', 'paragraphStyle' => 'Body A', 'baseIndent' => 0];

    /**
     * Save the report to a stable path (same key = same file, overwritten
     * on every export) so repeat downloads always reuse the same location.
     */
    protected static function storeDocx(TemplateProcessor $processor, string $key): string
    {
        Storage::disk('local')->makeDirectory('exports');

        $path = Storage::disk('local')->path("exports/{$key}.docx");

        $processor->saveAs($path);

        return $path;
    }

    /**
     * Replace the paragraph holding ${$macro} with the RichEditor HTML
     * rendered as WordprocessingML, so bold/italic/underline, headings,
     * lists and line breaks survive the export.
     */
    protected static function setRichText(TemplateProcessor $processor, string $macro, ?string $html, string $font = 'Times New Roman', ?string $paragraphStyle = 'Body A', int $baseIndent = 0): void
    {
        static::$richStyle = compact('font', 'paragraphStyle', 'baseIndent');

        try {
            $processor->replaceXmlBlock($macro, static::htmlToWordXml($html));
        } finally {
            static::$richStyle = ['font' => 'Times New Roman', 'paragraphStyle' => 'Body A', 'baseIndent' => 0];
        }
    }

    /**
     * @return string one or more <w:p> elements
     */
    public static function htmlToWordXml(?string $html): string
    {
        if (blank($html)) {
            return static::wordParagraph('');
        }

        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8"?><body>'.$html.'</body>', LIBXML_NOERROR | LIBXML_NOWARNING);

        return static::wordBlocks($dom->getElementsByTagName('body')->item(0));
    }

    /**
     * Turns the children of a block container into paragraphs. Loose inline
     * content is grouped into one paragraph; $marker (a list bullet/number)
     * is consumed by the first paragraph emitted.
     */
    protected static function wordBlocks(\DOMNode $parent, int $depth = 0, ?string $marker = null): string
    {
        $xml = '';
        $runs = '';

        $flush = function () use (&$xml, &$runs, &$marker, $depth): void {
            if (trim(strip_tags($runs)) === '' && ! str_contains($runs, '<w:br/>')) {
                $runs = '';

                return;
            }

            $xml .= static::wordParagraph($runs, $depth, $marker);
            $marker = null;
            $runs = '';
        };

        foreach ($parent->childNodes as $child) {
            $name = strtolower($child->nodeName);

            if (in_array($name, ['ul', 'ol'], true)) {
                $flush();
                $number = (int) ($child->attributes?->getNamedItem('start')?->nodeValue ?: 1);

                foreach ($child->childNodes as $item) {
                    if (strtolower($item->nodeName) === 'li') {
                        $itemMarker = $name === 'ul' ? '•' : ($number++).'.';
                        $xml .= static::wordBlocks($item, $depth + 1, $itemMarker);
                    }
                }
            } elseif (in_array($name, ['p', 'div', 'blockquote', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
                $flush();
                $inner = preg_match('/^h\d$/', $name) === 1
                    ? static::wordRuns($child, ['b' => true])
                    : static::wordRuns($child);

                if (static::hasBlockChild($child)) {
                    $xml .= static::wordBlocks($child, $depth, $marker);
                    $marker = null;
                } else {
                    $runs = $inner;
                    $flush();
                }
            } else {
                $runs .= static::wordRuns($child);
            }
        }

        $flush();

        return $xml;
    }

    protected static function hasBlockChild(\DOMNode $node): bool
    {
        foreach ($node->childNodes as $child) {
            if (in_array(strtolower($child->nodeName), ['ul', 'ol', 'p', 'div', 'blockquote', 'li'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{b?: bool, i?: bool, u?: bool}  $format
     */
    protected static function wordRuns(\DOMNode $node, array $format = []): string
    {
        if ($node instanceof \DOMText) {
            $text = preg_replace('/\s+/u', ' ', $node->nodeValue);

            return $text === '' ? '' : static::wordRun($text, $format);
        }

        $name = strtolower($node->nodeName);

        if ($name === 'br') {
            return '<w:r><w:br/></w:r>';
        }

        $format = match ($name) {
            'strong', 'b' => [...$format, 'b' => true],
            'em', 'i' => [...$format, 'i' => true],
            'u' => [...$format, 'u' => true],
            default => $format,
        };

        $xml = '';

        foreach ($node->childNodes as $child) {
            $xml .= static::wordRuns($child, $format);
        }

        return $xml;
    }

    /**
     * @param  array{b?: bool, i?: bool, u?: bool}  $format
     */
    protected static function wordRun(string $text, array $format = []): string
    {
        $font = static::$richStyle['font'];
        $props = '<w:rFonts w:ascii="'.$font.'" w:hAnsi="'.$font.'" w:cs="'.$font.'"/>'
            .(empty($format['b']) ? '' : '<w:b/><w:bCs/>')
            .(empty($format['i']) ? '' : '<w:i/><w:iCs/>')
            .(empty($format['u']) ? '' : '<w:u w:val="single"/>')
            .'<w:sz w:val="24"/><w:szCs w:val="24"/>';

        return '<w:r><w:rPr>'.$props.'</w:rPr><w:t xml:space="preserve">'
            .htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r>';
    }

    protected static function wordParagraph(string $runs, int $depth = 0, ?string $marker = null): string
    {
        $left = static::$richStyle['baseIndent'] + 720 * $depth;
        $indent = match (true) {
            $marker !== null => '<w:ind w:left="'.$left.'" w:hanging="360"/>',
            $left > 0 => '<w:ind w:left="'.$left.'"/>',
            default => '',
        };
        $style = static::$richStyle['paragraphStyle'];

        if ($marker !== null) {
            $runs = static::wordRun($marker).'<w:r><w:tab/></w:r>'.$runs;
        }

        return '<w:p><w:pPr>'.($style ? '<w:pStyle w:val="'.$style.'"/>' : '').'<w:spacing w:after="120"/>'.$indent.'</w:pPr>'.$runs.'</w:p>';
    }

    protected static function convertDocxToPdf(string $docxPath): ?string
    {
        $response = Http::attach('files', file_get_contents($docxPath), basename($docxPath))
            ->post(config('services.gotenberg.url').'/forms/libreoffice/convert');

        if ($response->failed()) {
            Notification::make()
                ->danger()
                ->title('PDF conversion failed')
                ->body('Gotenberg returned an error converting the report to PDF.')
                ->send();

            return null;
        }

        return $response->body();
    }
}
