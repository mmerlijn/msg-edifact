<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;

it('can get segment', function () {
    $edifact = new Edifact("PAD+New Street:10a::Down Town:1000BB+0123456789'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->patient->address->street)->toBe('New Street')
        ->and($msg->patient->address->building)->toBe('10 a')
        ->and($msg->patient->address->city)->toBe('Down Town')
        ->and($msg->patient->address->postcode)->toBe('1000BB')
        ->and($msg->patient->phones[0]->number)->toBe('0123456789');
});

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();
    $msg->patient->setAddress(new Address(postcode: "1000AA", city: "Amsterdam", street: "Street", building: "8"));
    $msg->patient->addPhone("1122334455");
    $edifact->setMsg($msg);
    $this->assertStringContainsString('PAD+Street:8::Amsterdam:1000AA+112 2334 455', $edifact->write());
});

it('can validate segment', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/patient_street re/');
    $this->expectExceptionMessageMatches('/patient_city re/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
