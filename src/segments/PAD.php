<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;


class PAD extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //get address
        $msg->patient->address = new Address(
            street: $this->getData(1), building: $this->getData(1, 1),
            city: $this->getData(1, 3), postcode: $this->getData(1, 4)
        );

        //get phone
        $msg->patient->addPhone($this->getData(2));

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set address
            ->setData($msg->patient->address->street, 1)
            ->setData($msg->patient->address->building, 1, 1)
            ->setData($msg->patient->address->city, 1, 3)
            ->setData($msg->patient->address->postcode, 1, 4)

            //phone
            ->setData($msg->patient->phones[0] ?? "", 2);

    }

    public function validate(): void
    {
        Validator::validate([
            "patient_street" => $this->data[1][0] ?? "",
            "patient_city" => $this->data[1][4] ?? "",
            "patient_building" => $this->data[1][1] ?? "",
            "patient_postcode" => $this->data[1][4] ?? "",
            "patient_telefoon" => $this->data[2][0] ?? "",
        ], [
            "patient_street" => 'required|max:30',
            "patient_city" => 'required|max:20',
            "patient_building" => 'max:8',
            "patient_postcode" => 'max:9',
            "patient_telefoon" => 'max:20',
        ], [
            "patient_street" => '@ PAD[1][0] set $msg->patient->address->street',
            "patient_city" => '@ PAD[1][4] set $msg->patient->address->city',
            "patient_building" => '@ PAD[1][1] adjust $msg->patient->address->building',
            "patient_postcode" => '@ PAD[1][4] adjust $msg->patient->address->postcode',
            "patient_telefoon" => '@ PAD[1][4] adjust $msg->patient->phone'
        ]);
    }
}