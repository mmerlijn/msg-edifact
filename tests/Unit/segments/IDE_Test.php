<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();
    $msg->order->complete = false;
    $msg->order->lab_nr = "112233";
    $edifact->setMsg($msg);
    expect(str_contains($edifact->write(), 'IDE:1+N+112233'))->toBeTrue();
});

it('can get segment', function () {
    $edifact = new Edifact("IDE:1+N+123456+'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->order->complete)->toBeFalse()
        ->and($msg->order->lab_nr)->toBe("123456");
});

it('can use validator', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/lab_nr required/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
