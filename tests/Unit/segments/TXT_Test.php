<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can get segment', function () {
    $edifact = new Edifact("PAD+Straat:16::Amsterdam:1000AA+0612341234'
TXT:1+Do the things you love'
TXT:2+Today or tomorrow'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->comments[0]->text)->toBe('Do the things you love')
        ->and($msg->comments[1]->text)->toBe('Today or tomorrow');
});

it('can set segment', function () {
    $msg = new Msg();
    $msg->msgType->type = "MEDVRI";
    $msg->addComment("Hello World!");
    $file = (new Edifact())->setMsg($msg)->write();
    expect($file)->toContain("TXT:1+Hello World!")
        ->and($file)->toContain("GGA") //only for medvri
        ->and($file)->toContain("GGO");
});
