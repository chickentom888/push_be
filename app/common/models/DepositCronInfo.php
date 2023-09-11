<?php

namespace Dcore\Models;

class DepositCronInfo extends BaseModel
{
    public $id;
    public $token_key;
    public $platform;
    public $network;
    public $contract;
    public $last_block;
    public $updated_at;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setSource('deposit_cron_info');
    }

}
