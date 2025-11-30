<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $msg->order->addRequest(new \mmerlijn\msgRepo\Request());
    $msg->order->addObservation(new \mmerlijn\msgRepo\Observation(
        test: new \mmerlijn\msgRepo\TestCode(code: 'ABC', value: 'ABCv'),done:false
    ));
    $msg->order->addObservation(new \mmerlijn\msgRepo\Observation(
        test: new \mmerlijn\msgRepo\TestCode(code: 'BCA', value: 'BCAv'), done:false
    ));
    $edifact = (new Edifact())->setMsg($msg);
    $file = $edifact->write();
    expect($file)->toContain("NUB:1:1+ABCv'")
        ->toContain("NUB:1:2+BCAv'");
});

it('can get segment', function () {
    $edifact = new Edifact("NUB:1:1+ABC'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->order->requests[0]->observations[0]->test->value)->toBe('ABC');
});


