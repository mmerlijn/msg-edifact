<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Phone;

class ARA extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //get contact name
        $msg->sender->name->name = $this->getData(1);
        //get contact phone
        if (!$msg->sender->phone)
            $msg->sender->setPhone($this->getData(2));
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set contact name
            ->setData($msg->sender->name->name, 1)
            //set contact phone
            ->setData((string)$msg->sender->phone, 2);
    }

    public function validate(): void
    {
        Validator::validate([
            "sender_name" => $this->data[1][0] ?? "",
            "sender_phone" => $this->data[2][0] ?? "",
        ], [
            "sender_name" => 'required',
            "sender_phone" => 'max:20',
        ], [
            "sender_name" => '@ ARA[1][0] set $msg->sender->name->name',
            "sender_phone" => '@ ARA[2][0] adjust $msg->sender->phone',
        ]);
    }
}