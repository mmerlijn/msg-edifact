<?php

namespace mmerlijn\msgEdifact\tests\Unit\segments;

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Phone;

class ZKHTest extends \mmerlijn\msgEdifact\tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->edifact = new Edifact("ZKH+ABC+Street:10b::?'s Sands:1000BB+0123-456789'");

    }

    public function test_name_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();

        $msg->sender->setOrganisation(['name' => 'ABC']);
        $edifact->setMsg($msg);
        $this->assertStringContainsString('ZKH+ABC', $edifact->write());
    }

    public function test_name_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame("ABC", $msg->sender->organisation->name);
//        $this->assertSame("ABC", $msg->sender->organisation->short_name);
        $this->assertSame("ABC", $array['sender']['organisation']['name']);
    }

    public function test_address_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();

        $msg->sender->address = new Address(postcode: "1000AA", city: "'s Grave", building: "34a", street: "Race Street");
        $edifact->setMsg($msg);
        $this->assertStringContainsString("Race Street:34 a::?'s Grave:1000AA", $edifact->write());
    }

    public function test_address_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame("Street", $msg->sender->address->street);
        $this->assertSame("10 b", $msg->sender->address->building);
        $this->assertSame("10", $msg->sender->address->building_nr);
        $this->assertSame("1000BB", $msg->sender->address->postcode);
        $this->assertSame("'s Sands", $msg->sender->address->city);
        $this->assertSame("Street", $array['sender']['address']['street']);
        $this->assertSame("1000BB", $array['sender']['address']['postcode']);
    }

    public function test_phone_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();

        $msg->sender->setPhone("1122334455");
        $edifact->setMsg($msg);
        $this->assertStringContainsString('+112 2334 455', $edifact->write());
    }

    public function test_phone_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame("012 3456 789", (string)$msg->sender->phone);
        $this->assertSame("012 3456 789", $array['sender']['phone']);
    }

    public function test_validation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/sender_name re/');
        $edifact = (new Edifact())->setMsg(new Msg());
        $edifact->write(true);

    }
}