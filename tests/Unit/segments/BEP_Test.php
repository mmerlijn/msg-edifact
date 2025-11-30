<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Comment;
use mmerlijn\msgRepo\Enums\ResultFlagEnum;
use mmerlijn\msgRepo\Msg;

it('can get observation', function () {
    $edifact =  new Edifact("BEP:1:1:5+0+Ht+0.40++l/l++0.35+0.50+HT  B'
BEP:1:1:6+0+Erythrocyten+4.4++10E12/l++3.8+5.6+ERY B MT'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->order->requests[0]->observations[0]->value)->toBe('0.40')
        ->and($msg->order->requests[0]->observations[0]->test->value)->toBe('Ht')
        ->and($msg->order->requests[0]->observations[0]->units)->toBe('l/l')
        ->and($msg->order->requests[0]->observations[0]->reference_range)->toBe('0.35-0.50')
        ->and($msg->order->requests[0]->observations[0]->test->code)->toBe('HT  B')
        ->and($msg->order->requests[0]->observations[1]->value)->toBe('4.4')
        ->and($msg->order->requests[0]->observations[1]->test->value)->toBe('Erythrocyten')
        ->and($msg->order->requests[0]->observations[1]->units)->toBe('10E12/l')
        ->and($msg->order->requests[0]->observations[1]->reference_range)->toBe('3.8-5.6')
        ->and($msg->order->requests[0]->observations[1]->test->code)->toBe('ERY B MT');
});

it('can set Observation', function(){
    $msg = new Msg();
    $msg->order->addRequest(new \mmerlijn\msgRepo\Request());
    $msg->order->addObservation(new \mmerlijn\msgRepo\Observation(
        value: 10,
        test: new \mmerlijn\msgRepo\TestCode(code: "AB  34 D", value: "ABCD"),
        units: "mmol",
        reference_range: "4-20",
        comments: [new Comment("today"), new Comment("tomorrow"), new Comment("yesterday")]
        ));
        $file = (new Edifact())->setMsg($msg)->write();
        expect($file)->toContain("OPB:1:1:1:1+today")
            ->and($file)->toContain("OPB:1:1:1:2+tomorrow")
            ->and($file)->toContain("OPB:1:1:1:3+yesterday")
            ->and($file)->toContain("BEP:1:1:1+0+ABCD+10++mmol++4+20+AB  34 D");
});

it('can set abnormal flag in Observation', function(){
    $msg = new Msg();
    $msg->order->addRequest(new \mmerlijn\msgRepo\Request());
    $msg->order->addObservation(new \mmerlijn\msgRepo\Observation(
        value: 10,
        test: new \mmerlijn\msgRepo\TestCode(code: "ABCD", value: "ABCD"),
        abnormal_flag: ResultFlagEnum::HIGH,
        ));
    $msg->order->addObservation(new \mmerlijn\msgRepo\Observation(
        value: 4,
        test: new \mmerlijn\msgRepo\TestCode(code: "DCBA", value: "DCBA"),
        abnormal_flag: ResultFlagEnum::LOW,
        ));
    $file = (new Edifact())->setMsg($msg)->write();
    expect($file)->toContain("BEP:1:1:1+0+ABCD+10+++>")
        ->and($file)->toContain("BEP:1:1:2+0+DCBA+4+++<");
});
