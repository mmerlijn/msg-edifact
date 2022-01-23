<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Organisation;
use mmerlijn\msgRepo\Phone;

class AFD extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        if (!$msg->sender->organisation)
            $msg->sender->setOrganisation();
        //get name
        $msg->sender->organisation->name = $this->getData(1);

        //get phone
        $msg->sender->organisation->setPhone($this->getData(2));
        if (!$msg->sender->phone)
            $msg->sender->setPhone($this->getData(2));
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set name
            ->setData($msg->sender->organisation?->name, 1)
            //set phone
            ->setData((string)$msg->sender->organisation?->phone ?: (string)$msg->sender->phone, 2);
    }

    public function validate(): void
    {
        Validator::validate([
            "sender_organisation_name" => $this->data[1][0] ?? "",
            "sender_organisation_phone" => $this->data[2][0] ?? "",
        ], [
            "sender_organisation_name" => 'required|max:70',
            "sender_organisation_phone" => 'max:20',
        ], [
            "sender_organisation_name" => '@ AFD[1][0] set/adjust $msg->sender->organisation->name',
            "sender_organisation_phone" => '@ AFD[2][0] adjust $msg->sender->organisation->phone',
        ]);
    }
}