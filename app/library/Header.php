<?php

namespace Dcore\Library;
class Header
{
    public $title;
    public $desc;
    public $key;
    public $image;
    public $canonial;

    /**
     * Header constructor.
     * @param $title
     * @param $desc
     * @param $key
     * @param $image
     * @param $canonial
     */
    public function __construct($title = null, $description = null, $keywords = null, $image = null, $canonial = null)
    {
        $this->title = $title;
        $this->desc = $description;
        $this->key = $keywords;
        $this->image = $image;
        $this->canonial = $canonial;
    }

}
