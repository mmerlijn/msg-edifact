<?php

namespace mmerlijn\msgEdifact\tests\Unit\segments;

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

class UNHTest extends \mmerlijn\msgEdifact\tests\TestCase
{

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->edifact = new Edifact("UNH+1122334455+MEDLAB:1'");

    }

    public function test_reference_nr_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();

        $msg->id = "5544332211";
        $edifact->setMsg($msg);
        $this->assertStringContainsString('UNH+5544332211', $edifact->write());
    }

    public function test_reference_nr_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $this->assertSame("1122334455", $msg->id);
        $array = $msg->toArray();
        $this->assertSame("1122334455", $array['id']);
    }

    public function test_message_type_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();

        $msg->msgType->type = "MEDLAB";
        $msg->msgType->version = 1;
        $edifact->setMsg($msg);
        $this->assertStringContainsString('+MEDLAB:1', $edifact->write());
    }

    public function test_message_type_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $this->assertSame("MEDLAB", $msg->msgType->type);
        $this->assertSame("1", $msg->msgType->version);
        $array = $msg->toArray();
        $this->assertSame("MEDLAB", $array['msgType']['type']);
        $this->assertSame("1", $array['msgType']['version']);
    }

    public function test_validation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/reference_id required failure/');
        $this->expectExceptionMessageMatches('/msg_type required failure/');
        $edifact = (new Edifact())->setMsg(new Msg());
        $edifact->write(true);

    }
}