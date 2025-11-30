<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Phone;

class ART extends Segment implements SegmentInterface
{//Wordt niet standaard gebruikt in Edifact
    public function getMsg(Msg $msg): Msg
    {
        //get requester
        $msg->order->requester->type = $this->getData(1); //type
        if (!$msg->receiver->agbcode) {
            $msg->receiver->agbcode = $this->getData(2);
            $msg->order->requester->agbcode = $this->getData(2);
        }
        $msg->order->requester->name->name = $this->getData(3);
        $msg->order->requester->setAddress(
            new Address(postcode: $this->getData(4, 4),
                city: $this->getData(4, 3),
                street: $this->getData(4),
                building: $this->getData(4, 1)
            ));
        //get phone
        $msg->order->requester->setPhone($this->getData(5));

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        if ($msg->order->requester->type) {
            $this->setData($msg->order->requester->type, 1); //default H
        }
        $this
            //set requester
            ->setData($msg->order->requester->agbcode, 2)
            ->setData($msg->order->requester->name->name, 3)
            ->setData($msg->order->requester->address?->street, 4)
            ->setData($msg->order->requester->address?->building, 4, 1)
            ->setData($msg->order->requester->address?->city, 4, 3)
            ->setData($msg->order->requester->address?->postcode, 4, 4)
            //phone
            ->setData((string)$msg->order->requester->phone, 5);
    }

    public function validate(): void
    {
        Validator::validate([
            "requester_type" => $this->data[1][0] ?? "",
            "requester_agbcode" => $this->data[2][0] ?? "",
            "requester_name" => $this->data[3][0] ?? "",
        ], [
            "requester_type" => 'required',
            "requester_agbcode" => 'required',
            "requester_name" => 'required',
        ], [
            "requester_type" => '@ ART[1][0] set $msg->order->requester->type',
            "requester_agbcode" => '@ ART[2][0] set $msg->order->requester->agbcode',
            "requester_name" => '@ ART[3][0] set $msg->order->requester->name->name',
        ]);
    }
}