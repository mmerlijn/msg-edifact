<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Enums\ResultFlagEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Observation;
use mmerlijn\msgRepo\Result;
use mmerlijn\msgRepo\TestCode;

class BEP extends Segment implements SegmentInterface
{
    public $repeat = true;

    public function getMsg(Msg $msg): Msg
    {
        if(!$msg->order->hasRequests()){
            $msg->order->addRequest(new \mmerlijn\msgRepo\Request());
        }
        //only measurements are stored
        if ($this->getData(1) == "0") { // value 1 is a comment
            $msg->order->addObservation(
                new Observation(
                    value: $this->getData(3),
                    test: new TestCode(
                        code: $this->getData(9),
                        value: $this->getData(2),
                    ),
                    units: $this->getData(5),
                    reference_range: trim($this->getData(7) . "-" . $this->getData(8), "-"),
                    abnormal_flag: ResultFlagEnum::set($this->getData(6)),
                    change: (bool)$this->getData(4)
                ));
        }
        return $msg;
    }

    public function setResult(Observation $observation): self
    {
        $this->setData("0", 1); // value 0 -> result, 1 -> comment
        $this->setData($observation->test->value, 2);
        $this->setData($observation->value, 3);
        $this->setData($observation->units, 5);
        if ($observation->reference_range) {
            $range = explode("-", $observation->reference_range);
            $this->setData($range[0] ?? "", 7);
            $this->setData($range[1] ?? "", 8);
        }
        if ($observation->abnormal_flag) {
            $this->setData($observation->abnormal_flag->getEdifact(), 6);
        }
        $this->setData($observation->test->code, 9);
        if ($observation->change) {
            $this->setData("C", 4); //TODO get the proper value
        }
        return $this;
    }

    public function validate(): void
    {
        Validator::validate([
            "measurement_type" => $this->data[1][0] ?? "",
            "measurement_name" => $this->data[2][0] ?? "",
            "measurement_code" => $this->data[9][0] ?? "",
            "measurement_value" => $this->data[3][0] ?? "",
        ], [
            "measurement_type" => 'required|length:1',
            "measurement_name" => 'required',
            "measurement_code" => 'required',
            "measurement_value" => 'required',
        ], [
            "measurement_type" => '@ BEP[1][0] set/adjust $msg->order->observation[..]->value',
            "measurement_name" => '@ BEP[2][0] set/adjust $msg->order->observation[..]->test_name',
            "measurement_code" => '@ BEP[9][0] set/adjust $msg->order->observation[..]->test_code',
            "measurement_value" => '@ BEP[3][0] set/adjust $msg->order->observation[..]->value',
        ]);
    }
}