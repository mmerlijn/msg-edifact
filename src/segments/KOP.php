<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Msg;

class KOP extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {

        //get name
        $msg->order->copy_to->name->name = $this->getData(2);

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this
            //copy report to
            ->setData("NAAR", 1)
            //set name
            ->setData($msg->order->copy_to->name->name, 2);
    }

    public function validate(): void
    {
        Validator::validate([
            "copy_to_name" => $this->data[2][0] ?? "",
        ], [
            "copy_to_name" => 'required',

        ], [
            "copy_to_name" => '@ KOP[2][0] set/adjust $msg->receiver->name',

        ]);
    }
}