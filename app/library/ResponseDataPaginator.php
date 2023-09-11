<?php

namespace Dcore\Library;
class ResponseDataPaginator
{
    public $data;
    public $paging;
    public $optional;

    /**
     * ResponseDataPaginator constructor.
     * @param $data
     * @param $paging
     * @param null $optional
     */
    public function __construct($data, $paging, $optional = null)
    {
        $this->data = $data;
        $this->paging = $paging;
        $this->optional = $optional;
    }
}
