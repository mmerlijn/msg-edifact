<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    //noting to test here, just coverage
})->skip();

it('can get segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();
    $msg->id = "11223344";
    $edifact->setMsg($msg);
    $aantal = count($edifact->segments) - 2; //first and last do not count
    $file = $edifact->write();
    expect($file)->toContain("UNT+" . $aantal . "+11223344'");

});

it('can be validated', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/message_id required failure/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
