<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
declare(strict_types = 1);

namespace Movim;

use Embed\Detectors\Detector;

class EmbedImagesExtractor extends Detector
{
    public function detect(): array
    {
        $document = $this->extractor->getDocument();
        $images = [];

        foreach ($document->select('.//img')->nodes() as $node) {
            $src = $node->getAttribute('src');

            $images[$src] = $this->extractor->resolveUri($src);
        }

        return $images;
    }
}
