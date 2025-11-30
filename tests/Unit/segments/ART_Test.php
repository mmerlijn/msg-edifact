<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();

    $msg->order->requester->agbcode = "0123456";
    $msg->order->requester->name->name = "M.T. Jobs";
    $msg->order->requester->setAddress(new Address(postcode: "1000BB", city: "Amsterdam", street: "Street", building: "5"));
    $msg->order->requester->setPhone("0623456789");
    $edifact->setMsg($msg);
    $file = $edifact->write();
    expect($file)->toContain("ART+H+0123456+M.T. Jobs+Street:5::Amsterdam:1000BB+06 2345 6789'");
});

it('can get segment', function () {
    $edifact = new Edifact("ART+H+0123456+M.C. Hart+Street:2::Amsterdam:1000AA+0123456789'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->order->requester->agbcode)->toBe("0123456")
        ->and($msg->order->requester->name->name)->toBe("M.C. Hart")
        ->and($msg->order->requester->address->street)->toBe("Street")
        ->and($msg->order->requester->address->building)->toBe("2")
        ->and($msg->order->requester->address->city)->toBe("Amsterdam")
        ->and($msg->order->requester->address->postcode)->toBe("1000AA")
        ->and((string)$msg->order->requester->phone)->toBe("012 3456 789");
});
it('can validated', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/requester_agbcode required/');
    $this->expectExceptionMessageMatches('/requester_name required/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
