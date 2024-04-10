<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Enums\ResultFlagEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Result;

class BEP extends Segment implements SegmentInterface
{
    public $repeat = true;

    public function getMsg(Msg $msg): Msg
    {
        //only measurements are stored
        if ($this->getData(1) == "0") { // value 1 is a comment
            $result = new Result();
            //get value
            $result->value = $this->getData(3);

            //identifiercode / labcode
            $result->test_code = $this->getData(9);
            $result->test_name = $this->getData(2);
            $result->units = $this->getData(5);
            $result->reference_range = trim($this->getData(7) . "-" . $this->getData(8), "-");
            $result->abnormal_flag = ResultFlagEnum::set($this->getData(6));
            $result->change = $this->getData(4) ? true : false;
            $msg->order->addResult($result);
        }
        return $msg;
    }

    public function setResult(Result $result): self
    {
        $this->setData("0", 1); // value 0 -> result, 1 -> comment
        $this->setData($result->test_name, 2);
        $this->setData($result->value, 3);
        $this->setData($result->units, 5);
        if ($result->reference_range) {
            $range = explode("-", $result->reference_range);
            $this->setData($range[0] ?? "", 7);
            $this->setData($range[1] ?? "", 8);
        }
        if ($result->abnormal_flag) {
            $this->setData($result->abnormal_flag->getEdifact(), 6);
        }
        $this->setData($result->test_code, 9);
        if ($result->change) {
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
            "measurement_type" => '@ BEP[1][0] set/adjust $msg->order->result[..]->value',
            "measurement_name" => '@ BEP[2][0] set/adjust $msg->order->result[..]->test_name',
            "measurement_code" => '@ BEP[9][0] set/adjust $msg->order->result[..]->test_code',
            "measurement_value" => '@ BEP[3][0] set/adjust $msg->order->result[..]->value',
        ]);
    }
}