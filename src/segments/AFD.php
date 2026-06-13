<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Organization;
use mmerlijn\msgRepo\Phone;

class AFD extends Segment implements SegmentInterface
{ //Wordt niet standaard gebruikt in Edifact
    public function getMsg(Msg $msg): Msg
    {
        if (!$msg->sender->organization)
            $msg->sender->setOrganization();
        //get department name
        $msg->sender->organization->department = $this->getData(1);

        //get phone
        $msg->sender->organization->setPhone($this->getData(2));
        if (!$msg->sender->phone)
            $msg->sender->setPhone($this->getData(2));
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set name
            ->setData($msg->sender->organization?->department, 1)
            //set phone
            ->setData((string)$msg->sender->organization?->phone ?: (string)$msg->sender->phone, 2);
    }

    public function validate(): void
    {
        Validator::validate([
            "sender_organization_department" => $this->data[1][0] ?? "",
            "sender_organization_phone" => $this->data[2][0] ?? "",
        ], [
            "sender_organization_department" => 'required|max:70',
            "sender_organization_phone" => 'max:20',
        ], [
            "sender_organization_department" => '@ AFD[1][0] set/adjust $msg->sender->organization->department',
            "sender_organization_phone" => '@ AFD[2][0] adjust $msg->sender->organization->phone',
        ]);
    }
}