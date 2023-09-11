<?php
/**
 * Created by PhpStorm.
 * User: Unknown
 */

namespace DCrypto\Object;

class Send
{
    const TYPEOF_METHOD_SEND_TO_ADDRESS = "sendtoaddress";
    const TYPEOF_METHOD_SEND_FROM_ACCOUNT = "sendfrom";
    const TYPEOF_METHOD_SEND_MOVE = "move";

    public $typeof_method;
    public $amount;
    public $fee;
    public $comment_from;
    public $comment_to;
    public $hash;
    public $info;
    public $wallet_pass_phrase;
    public $nonce;
    public $with_nonce;
    public $multiply_gas;
    public $signed_data;
    public $tx_param;
    public $gas_limit;
    public $gas_price;

    /**
     * @return mixed
     */
    public function getTypeofMethod()
    {
        return $this->typeof_method;
    }

    /**
     * @param mixed $typeof_method
     */
    public function setTypeofMethod($typeof_method)
    {
        $this->typeof_method = $typeof_method;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return floatval($this->amount);
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = floatval($amount);
    }

    /**
     * @return mixed
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param mixed $fee
     */
    public function setFee($fee)
    {
        $this->fee = $fee;
    }

    /**
     * @return mixed
     */
    public function getCommentFrom()
    {
        return $this->comment_from;
    }

    /**
     * @param mixed $comment_from
     */
    public function setCommentFrom($comment_from)
    {
        $this->comment_from = $comment_from;
    }

    /**
     * @return mixed
     */
    public function getCommentTo()
    {
        return $this->comment_to;
    }

    /**
     * @param mixed $comment_to
     */
    public function setCommentTo($comment_to)
    {
        $this->comment_to = $comment_to;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getWalletPassPhrase()
    {
        return $this->wallet_pass_phrase;
    }

    /**
     * @param mixed $wallet_pass_phrase
     */
    public function setWalletPassPhrase($wallet_pass_phrase)
    {
        $this->wallet_pass_phrase = $wallet_pass_phrase;
    }

    /**
     * @return mixed
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @param mixed $nonce
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }

}
