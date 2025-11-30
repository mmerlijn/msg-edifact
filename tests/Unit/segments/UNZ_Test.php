<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();

    $msg->processing_id = "AB123123123";
    $edifact->setMsg($msg);
    expect($edifact->write())->toContain("UNZ+1+AB123123123");
});

it('can get segment', function () {
    $edifact = new Edifact("UNZ+1+AB123123123'");
})->skip();

it('can be validated', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/processing_id required failure/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
