<?php

namespace mmerlijn\msgEdifact\tests\Unit\segments;

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgEdifact\tests\TestCase;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\OrderItem;
use mmerlijn\msgRepo\Result;

class OPBTest extends TestCase
{
    public $edifact;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->edifact = new Edifact("BEP:1:1:5+0+Ht+0.40++l/l++0.35+0.50+HT  B'
BEP:1:1:6+0+Gwefsfwe+4.4++10E12/l++3.8+5.6+SQW B MT'
OPB:1:1:6:1+             The value is medium'");
    }

    public function test_order_and_comment_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame('0.40', $msg->order->results[0]->value);
        $this->assertSame('0.40', $array['order']['results'][0]['value']);
        $this->assertSame('The value is medium', $msg->order->results[1]->comments[0]);
    }

    public function test_order_and_comment_setter()
    {
        $msg = new Msg();
        $msg->order->addResult(new Result(value: 10, test_code: "AB  34 D", test_name: "ABCD", comments: ["today", "tomorrow", "yesterday"], units: "mmol"));
        $file = (new Edifact())->setMsg($msg)->write();
        $this->assertStringContainsString("OPB:1:1:1:1+today", $file);
        $this->assertStringContainsString("OPB:1:1:1:2+tomorrow", $file);
        $this->assertStringContainsString("OPB:1:1:1:3+yesterday", $file);
        $this->assertStringContainsString("BEP:1:1:1+0+ABCD+10++mmol++++AB  34 D", $file);

    }
}