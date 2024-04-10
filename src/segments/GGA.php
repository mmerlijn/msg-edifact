<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Organisation;
use mmerlijn\msgRepo\Phone;

//MEDVRI
class GGA extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //sender name
        $msg->sender->setOrganisation(new Organisation(short: $this->getData(1)));

        //department
        $msg->sender->organisation->department = $this->getData(2);

        //get name
        $msg->sender->organisation->name = $this->getData(3);

        //sender address
        $msg->sender->setAddress(new Address(
            postcode: $this->getData(4, 4),
            city: $this->getData(4, 3),
            street: $this->getData(4),
            building: $this->getData(4, 1)
        ));

        if (!$msg->sender->phone)
            $msg->sender->setPhone($this->getData(5));
        if (!$msg->sender->organisation->phone)
            $msg->sender->organisation->setPhone($this->getData(5));
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set name
            ->setData($msg->sender->organisation?->short ?: $msg->sender->organisation?->name, 1)
            ->setData($msg->sender->organisation?->department, 2)
            ->setData($msg->sender->organisation?->name, 3)
            //address
            ->setData($msg->sender->address?->street, 4)
            ->setData($msg->sender->address?->building, 4, 1)
            ->setData($msg->sender->address?->city, 4, 3)
            ->setData($msg->sender->address?->postcode, 4, 4)
            //set phone
            ->setData((string)$msg->sender->phone ?: (string)$msg->sender->organisation?->phone, 5);
    }

    public function validate(): void
    {
        Validator::validate([
            "sender_name" => $this->data[1][0] ?? "",
            "sender_department" => $this->data[2][0] ?? "",
            "sender_facility" => $this->data[3][0] ?? "",
            "sender_street" => $this->data[4][0] ?? "",
            "sender_building" => $this->data[4][1] ?? "",
            "sender_city" => $this->data[4][3] ?? "",
            "sender_postcode" => $this->data[4][4] ?? "",
        ], [
            "sender_name" => 'required|max:70',
            "sender_department" => 'required',
            "sender_facility" => 'required',
            "sender_street" => "required",
            "sender_building" => "required",
            "sender_city" => "required",
            "sender_postcode" => "required",
        ], [
            "sender_name" => '@ GGA[1][0] set/adjust $msg->sender->organisation->name',
            "sender_department" => '@ GGA[2][0] set/adjust $msg->sender->organisation->department',
            "sender_facility" => '@ GGA[3][0] set/adjust $msg->sender->organisation->name',
            "sender_street" => '@ GGA[][0] set/adjust $msg->sender->address->street',
            "sender_building" => '@ GGA[][0] set/adjust $msg->sender->address->building',
            "sender_city" => '@ GGA[][0] set/adjust $msg->sender->address->city',
            "sender_postcode" => '@ GGA[][0] set/adjust $msg->sender->address->postcode',
        ]);
    }
}