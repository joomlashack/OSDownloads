<?php
/**
 * @package   AllediaFramework
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Content;

use Alledia\Framework\Object;
use Alledia\Framework\Content\Tag;

defined('_JEXEC') or die();

class Text extends Object
{
    public $content = '';

    /**
     * Constructor method, that defines the internal content
     *
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Extract multiple {mytag} tags from the content
     *
     * @param  string $tagName
     * @return array  An array with all tags {tagName} found on the text
     */
    protected function extractTags($tagName)
    {
        preg_match_all(Tag::getRegex($tagName), $this->content, $matches);

        return $matches[0];
    }

    /**
     * Extract multiple {mytag} tags from the content, returning
     * as Tag instances
     *
     * @param  string $tagName
     * @return array  An array with all tags {tagName} found on the text
     */
    public function getTags($tagName)
    {
        $unparsedTags = $this->extractTags($tagName);

        $tags = array();
        foreach ($unparsedTags as $unparsedTag) {
            $tags[] = new Tag($tagName, $unparsedTag);
        }

        return $tags;
    }
}
