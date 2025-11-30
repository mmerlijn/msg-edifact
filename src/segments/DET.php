<?php

namespace mmerlijn\msgEdifact\segments;

use Carbon\Carbon;
use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Phone;

class DET extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //get name
        $msg->order->observation_at = Carbon::createFromFormat("ymdHi",
            $this->getData(1) .
            $this->getData(1, 1) .
            $this->getData(1, 2) .
            $this->getData(2) .
            $this->getData(2, 1)
        );
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //set name
            ->setData($msg->order->observation_at?->format("y"), 1)
            ->setData($msg->order->observation_at?->format("m"), 1, 1)
            ->setData($msg->order->observation_at?->format("d"), 1, 2)
            ->setData($msg->order->observation_at?->format("H"), 2)
            ->setData($msg->order->observation_at?->format("i"), 2, 1);
    }

    public function validate(): void
    {
        Validator::validate([
            "observation_datetime" => ($this->data[1][0] ?? "") . ($this->data[1][1] ?? "") . ($this->data[1][2] ?? ""),
        ], [
            "observation_datetime" => 'required|length:6',
        ], [
            "observation_datetime" => '@ DET[1][0] / DET[1][1] / DET[1][2] set/adjust $msg->order->dt_of_observation',
        ]);
    }
}