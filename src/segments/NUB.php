<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Observation;
use mmerlijn\msgRepo\Request;
use mmerlijn\msgRepo\Result;
use mmerlijn\msgRepo\TestCode;

class NUB extends Segment implements SegmentInterface
{
    public function getMsg(Msg $msg): Msg
    {
        if(!$msg->order->hasRequests()){
            //no orderItem yet, create one
            $msg->order->addRequest(new Request());
        }
        //get orderItem
        $msg->order->addObservation(new Observation(test: new TestCode(value: $this->getData(1)), done: false));
        return $msg;
    }

    public function setResult(Observation $result): self
    {
        if(!$result->done){
            $this->setData($result->test->value, 1);
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
            "nub_field" => '@ NUB[1][0] set $msg->order->requests[..]->observations[..]->test->value',
        ]);
    }
}