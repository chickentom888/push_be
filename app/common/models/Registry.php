<?php

namespace DCore\Models;

class Registry extends BaseModel
{
    public $id;
    public $last_block;
    public $eth_rate;
    public $bnb_rate;

    public function initialize()
    {
        $this->setSource("registry");
    }
}
