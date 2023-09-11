<?php

namespace Dcore\Modules\Admin\Controllers;


class IndexController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        parent::initialize(['check-login']);
        $this->setKeyActive('dashboard');
    }

    public function indexAction()
    {

    }

}

