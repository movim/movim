<?php

namespace App\Widgets\AvatarPlaceholder;

use Cocur\Slugify\Slugify;

class AvatarPlaceholder extends \Movim\Widget\Base
{
    public function display()
    {
        $id = urldecode($this->get('id'));
        $size = $this->get('size');

        $letters = firstLetterCapitalize((new Slugify())->slugify($id));
        header_remove('Content-Type');
        header('Content-Type: image/svg+xml');
        header('Cache-Control: max-age=' . 3600 * 24);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $svg = $dom->createElementNS('http://www.w3.org/2000/svg', 'svg');
        $svg->setAttribute('version', '1.0');
        $svg->setAttribute('width', '100px');
        $svg->setAttribute('height', '100px');
        $dom->appendChild($svg);

        $defs = $dom->createElement('defs');
        $svg->appendChild($defs);

        $style = $dom->createElement('style', '@import url(\'//theme/fonts/Roboto/font.css\');
        #avatar {
          font-family: "Roboto", sans-serif;
          fill: white;
        }');
        $style->setAttribute('type', 'text/css');
        $defs->appendChild($style);

        $g = $dom->createElement('g');
        $g->setAttribute('id', 'avatar');
        $svg->appendChild($g);

        $rect = $dom->createElement('rect');
        $rect->setAttribute('width', '100');
        $rect->setAttribute('height', '100');
        $rect->setAttribute('fill', palette()[stringToColor($id)]);
        $rect->setAttribute('x', '0');
        $rect->setAttribute('y', '0');
        $g->appendChild($rect);

        $text = $dom->createElement('text');
        $text->appendChild($dom->createTextNode($letters));
        $text->setAttribute('width', '100');
        $text->setAttribute('height', '100');
        $text->setAttribute('x', '50');
        $text->setAttribute('y', '66');
        $text->setAttribute('fill', 'white');
        $text->setAttribute('text-anchor', 'middle');
        $text->setAttribute('style', 'font-size: 2.75rem;');
        $g->appendChild($text);

        echo $dom->saveXML();
        exit;
    }
}
