<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Enums\PatientSexEnum;
use mmerlijn\msgRepo\Msg;

class PID extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //get dob
        $msg->patient->setDob($this->getData(1) . "-" . $this->getData(1, 1) . "-" . $this->getData(1, 2));

        //get sex
        $msg->patient->setSex($this->getData(2));

        //get patient name
        $msg->patient->name->initials = $this->getData(3, 5);
        if ($msg->patient->sex == PatientSexEnum::FEMALE) {
            $msg->patient->name->own_lastname = $this->getData(3, 2);
            $msg->patient->name->own_prefix = $this->getData(3, 3);
            $msg->patient->name->lastname = $this->getData(3);
            $msg->patient->name->prefix = $this->getData(3, 1);
        } else {
            $msg->patient->name->own_lastname = $this->getData(3);
            $msg->patient->name->own_prefix = $this->getData(3, 1);
        }
        //get patient reference nr sender ??

        //get BSN
        $msg->patient->setBsn(substr($this->getData(5), 3));

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set dob
            ->setData($msg->patient->dob?->format('Y'), 1)
            ->setData($msg->patient->dob?->format('m'), 1, 1)
            ->setData($msg->patient->dob?->format('d'), 1, 2)

            //set sex
            ->setData($msg->patient->sex->getEdifact(), 2)

            //set name
            ->setData($msg->patient->name->initials, 3, 5);
        if ($msg->patient->sex == PatientSexEnum::MALE) {
            $this->setData($msg->patient->name->own_lastname, 3)
                ->setData($msg->patient->name->own_prefix, 3, 1);
        } else {
            $this->setData($msg->patient->name->lastname, 3)
                ->setData($msg->patient->name->prefix, 3, 1)
                ->setData($msg->patient->name->own_lastname, 3, 2)
                ->setData($msg->patient->name->own_prefix, 3, 3);
        }
        $this->setData("BSN" . $msg->patient->getBsn(), 5);
    }

    public function validate(): void
    {
        Validator::validate([
            "dob" => ($this->data[1][0] ?? "") . ($this->data[1][1] ?? "") . ($this->data[1][2] ?? ""),
            "sex" => $this->data[2][0] ?? "",
            "patient_lastname" => $this->data[3][0] ?? "",
            "patient_prefix" => $this->data[3][1] ?? "",
        ], [
            "dob" => 'required|length:8',
            "sex" => 'required|length:1',
            "patient_lastname" => 'max:30',
            "patient_prefix" => 'max:8',
        ], [
            "dob" => '@ PID[1][0] / PID[1][1] / PID[1][2] set $msg->patient->dob',
            "sex" => '@ PID[2][0] set $msg->patient->sex',
            "patient_lastname" => '@ PID[3][0] adjust $msg->patient->name->lastname',
            "patient_prefix" => '@ PID[3][1] adjust $msg->patient->name->prefix',
        ]);
    }
}