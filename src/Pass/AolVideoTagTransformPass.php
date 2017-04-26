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
 * Class AolVideoTagTransformPass
 * @package Lullabot\AMP\Pass
 *
 * <div id="58f792d4f3bdc915f50c5ad8" class="vdb_player vdb_58f792d4f3bdc915f50c5ad85638b820e4b0b130488d0bda">
 * <script async type="text/javascript" src="//delivery.vidible.tv/jsonp/pid=58f792d4f3bdc915f50c5ad8/5638b820e4b0b130488d0bda.js"></script>
 * </div>
 *
 */
class AolVideoTagTransformPass extends BasePass
{
    const DOM_SELECTOR = 'div.vdb_player';

    function pass()
    {
        $videoElements = $this->q->find(self::DOM_SELECTOR);

        foreach ($videoElements as $video) {
            $videoDomElement = $video->get(0);
            $tagName = self::DOM_SELECTOR;
            $lineNumber = $this->getLineNo($videoDomElement);
            $contextString = $this->getContextString($videoDomElement);

            $attributes = $this->getVideoAttributes($video);

            $video->after('<amp-o2-player></amp-o2-player>');
            $newVideoElement = $video->next();
            $newVideoElement->attr($attributes);
            $newVideoDomElement = $newVideoElement->get(0);

            $video->remove();
            $this->addActionTaken(new ActionTakenLine(
                $tagName,
                ActionTakenType::AOL_VIDEO_SCRIPT_CONVERTED,
                $lineNumber,
                $contextString
            ));
            $this->context->addLineAssociation($newVideoDomElement, $lineNumber);
        }

        return $this->transformations;
    }

    /**
     * @param DOMQuery $divElement
     * @return array
     */
    protected function getVideoAttributes(DOMQuery $divElement)
    {
        $attributes = array();

        $idMatches = preg_match('/vdb_([a-z0-9]{24})([a-z0-9]{24})/', $divElement->attr('class'), $matches);
        if (false != $idMatches) {
            $attributes['data-pid'] = $matches[1];
            $attributes['data-bcid'] = $matches[2];
        }

        $attributes['height'] = '200';
        $attributes['width'] = '320';
        $attributes['layout'] = 'responsive';

        return $attributes;
    }
}
