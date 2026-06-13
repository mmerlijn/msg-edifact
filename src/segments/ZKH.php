<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;

class ZKH extends Segment implements SegmentInterface
{

    public function getMsg(Msg $msg): Msg
    {
        if (!$msg->sender->organization) {
            $msg->sender->setOrganization();
        }
        //sender name
        $msg->sender->organization->short = $this->getData(1);
        if (!$msg->sender->organization->name)
            $msg->sender->organization->name = $this->getData(1);

        //sender address
        $msg->sender->address = new Address(
            postcode: $this->getData(2, 4),
            city: $this->getData(2, 3),
            street: $this->getData(2),
            building: $this->getData(2, 1)
        );

        //sender phone
        $msg->sender->setPhone($this->getData(3));

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //sender name
            ->setData($msg->sender->organization?->short ?: $msg->sender->organization?->name, 1)
            //address
            ->setData($msg->sender->address?->street, 2)
            ->setData($msg->sender->address?->building, 2, 1)
            ->setData($msg->sender->address?->city, 2, 3)
            ->setData($msg->sender->address?->postcode, 2, 4)
            ->setData($msg->sender->address?->postbus, 2, 2)
            //phone
            ->setData((string)$msg->sender->phone, 3);
    }

    public function validate(): void
    {
        Validator::validate([
            'sender_name' => $this->data[1][0] ?? "",
            "sender_street" => $this->data[2][0] ?? "",
            "sender_building" => $this->data[2][1] ?? "",
            "sender_city" => $this->data[2][3] ?? "",
            "sender_postcode" => $this->data[2][4] ?? "",
        ], [
            'sender_name' => "required",
            "sender_street" => 'required',
            "sender_building" => 'required',
            "sender_city" => 'required',
            "sender_postcode" => 'required',
        ], [
            'sender_name' => '@ ZKH[1][0] set $msg->sender->organization->short',
            "sender_street" => '@ ZKH[2][0] set $msg->sender->address->street',
            "sender_building" => '@ ZKH[2][1] set $msg->sender->address->building',
            "sender_city" => '@ ZKH[2][3] set $msg->sender->address->city',
            "sender_postcode" => '@ ZKH[2][4] set $msg->sender->address->postcode',
        ]);
    }
}