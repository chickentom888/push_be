<?php

namespace Dcore\Modules\Admin\Controllers;


use Dcore\ControllerBase\ControllerBase;

class ExtendedControllerBase extends ControllerBase
{
    public function initialize($param = null)
    {
        $paramCheck = $param ? $param : ['check-login', 'check-role' => [self::ROLE_ADMIN]];
        parent::initialize($paramCheck);
    }
}
