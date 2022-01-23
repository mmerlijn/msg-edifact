<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Result;

class NUB extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        //get orderItem
        $msg->order->addResult(new Result(done: false, test_name: $this->getData(1)));
        return $msg;
    }

    public function setResult(Result $result): self
    {
        if (!$result->done) {
            $this->setData($result->test_name, 1);
        }
        return $this;
    }

    public function validate(): void
    {
        Validator::validate([
            "nub_field" => $this->data[1][0] ?? "",
        ], [
            "nub_field" => 'required',

        ], [
            "nub_field" => '@ NUB[1][0] set $msg->order->result[..]->test_name',
        ]);
    }
}