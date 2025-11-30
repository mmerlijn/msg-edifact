<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Comment;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\TestCode;

it('can set segment', function () {
    $msg = new Msg();
    $msg->order->addRequest(new \mmerlijn\msgRepo\Request());
    $msg->order->addObservation(
        new \mmerlijn\msgRepo\Observation(
            value: 10,
            test: new TestCode(code: "AB  34 D", value: "ABCD"),
            units: "mmol",
            comments: [new Comment("today"), new Comment("tomorrow"), new Comment("yesterday")]
        ));
    $file = (new Edifact())->setMsg($msg)->write();
    expect($file)->toContain("OPB:1:1:1:1+today")
        ->and($file)->toContain("OPB:1:1:1:2+tomorrow")
        ->and($file)->toContain("OPB:1:1:1:3+yesterday")
        ->and($file)->toContain("BEP:1:1:1+0+ABCD+10++mmol++++AB  34 D");
});

it('can get segment', function () {
    $edifact = new Edifact("BEP:1:1:5+0+Ht+0.40++l/l++0.35+0.50+HT  B'
BEP:1:1:6+0+Gwefsfwe+4.4++10E12/l++3.8+5.6+SQW B MT'
OPB:1:1:6:1+             The value is medium'");

    $msg = $edifact->getMsg(new Msg());
    expect($msg->order->requests[0]->observations[0]->value)->toBe('0.40')
        ->and($msg->order->requests[0]->observations[1]->comments[0]->text)->toBe('The value is medium');
    });
