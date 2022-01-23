<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;


class GGO extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //get name
        $msg->receiver->setName(new Name(name: $this->getData(1)));

        //receiver address
        $msg->receiver->address = new Address(
            street: $this->getData(4),
            building: $this->getData(4, 1),
            city: $this->getData(4, 3),
            postcode: $this->getData(4, 4)
        );
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set name
            ->setData($msg->receiver->name->name, 1)
            //address
            ->setData($msg->receiver->address?->street, 4)
            ->setData($msg->receiver->address?->building, 4, 1)
            ->setData($msg->receiver->address?->city, 4, 3)
            ->setData($msg->receiver->address?->postcode, 4, 4)
            //set phone
            ->setData((string)$msg->receiver->phone, 5);
    }

    public function validate(): void
    {
        Validator::validate([
            "receiver_name" => $this->data[1][0] ?? "",
        ], [
            "receiver_name" => 'required|max:70',

        ], [
            "receiver_name" => '@ GGO[1][0] set/adjust $msg->receiver->name->name',

        ]);
    }
}