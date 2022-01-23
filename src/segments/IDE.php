<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Msg;

class IDE extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //order complete
        $msg->order->complete = ($this->getData(1) == "N") ? false : true;

        //identification nr (lab_nr)
        $msg->order->lab_nr = $this->getData(2);

        //material

        //volume

        return $msg;
    }

    public function setMsg(Msg $msg): void
    {

        $this
            //order complete
            ->setData($msg->order->complete ? "J" : "N", 1)

            //identification nr (lab_nr)
            ->setData($msg->order->lab_nr, 2);

        //material

        //volume
    }

    public function validate(): void
    {
        Validator::validate([
            "order_complete" => $this->data[1][0] ?? "",
            "lab_nr" => $this->data[2][0] ?? "",
        ], [
            "order_complete" => 'required|length:1',
            "lab_nr" => 'required',
        ], [
            "order_complete" => '@ IDE[1][0] set/adjust $msg->order->complete',
            "lab_nr" => '@ IDE[2][0] adjust $msg->order->lab_nr',
        ]);
    }
}