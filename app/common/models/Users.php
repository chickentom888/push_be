<?php

namespace Dcore\Models;

class Users extends BaseModel
{
    public $id;
    public $username;
    public $password;
    public $name;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setSource('users');
    }
}
