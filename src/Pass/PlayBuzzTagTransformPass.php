<?php
/*
 * Copyright 2016 Google
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


namespace Lullabot\AMP\Pass;

use QueryPath\DOMQuery;

use Lullabot\AMP\Utility\ActionTakenLine;
use Lullabot\AMP\Utility\ActionTakenType;

/**
 * Class IframeDailymotionTagTransformPass
 * @package Lullabot\AMP\Pass
 *
 * Sample PlayBuzz script code:
 * <script type="text/javascript" src="//cdn.playbuzz.com/widget/feed.js"></script>
 * <div class="pb_feed" data-embed-by="121a4d19-1e1e-45a1-96e1-548002beef29"
 * data-item="ad71e08f-5346-4014-9c66-6efd93ab8c9e" data-recommend="false" data-game-info="false"
 * data-comments="false"></div>
 *
 */
class PlayBuzzTagTransformPass extends BasePass
{
    const DOM_SELECTOR = 'div.pb_feed';

    function pass()
    {
        $playbuzzElements = $this->q->find(DOM_SELECTOR);

        foreach ($playbuzzElements as $pb) {
            $pbDomElement = $pb->get(0);
            $tagName = DOM_SELECTOR;
            $lineNumber = $this->getLineNo($pbDomElement);
            $contextString = $this->getContextString($pbDomElement);

            $attributes = $this->getPlayBuzzAttributes($pb);

            $pb->after('<amp-playbuzz></amp-playbuzz>');
            $newPbElement = $pb->next();
            $newPbElement->attr($attributes);
            $newPbDomElement = $newPbElement->get(0);

            $pb->remove();
            $this->addActionTaken(new ActionTakenLine(
                $tagName,
                ActionTakenType::PLAYBUZZ_SCRIPT_CONVERTED,
                $lineNumber,
                $contextString
            ));
            $this->addLineAssociation($newPbDomElement, $lineNumber);
        }

        return $this->transformations;
    }

    /**
     * @param DOMQuery $divElement
     * @return array
     */
    protected function getPlayBuzzAttributes(DOMQuery $divElement)
    {
        $attributes = array();

        $itemId = $divElement->attr('data-item');
        if (null !== $itemId) {
            $attributes['data-item'] = $itemId;
        }

        $share = $divElement->attr('data-recommend');
        if (null !== $share) {
            $attributes['data-share-buttons'] = $share;
        }

        $comments = $divElement->attr('data-comments');
        if (null !== $comments) {
            $attributes['data-comments'] = $comments;
        }

        return $attributes;
    }
}
