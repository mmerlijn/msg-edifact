<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Msg;


class UNT extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //no important data
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {
        $this->setData($msg->id, 2);
    }

    public function validate(): void
    {
        Validator::validate([
            "message_id" => $this->data[2][0] ?? "",
        ], [
            "message_id" => 'required',
        ], [
            "message_id" => '@ UNT[2][0] set/adjust $msg->id',
        ]);
    }
}