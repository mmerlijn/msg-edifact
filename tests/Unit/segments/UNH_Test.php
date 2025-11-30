<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can get segment', function () {
    $edifact = new Edifact("UNH+1122334455+MEDLAB:1'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->id)->toBe("1122334455")
    ->and($msg->msgType->type)->toBe("MEDLAB")
        ->and($msg->msgType->version)->toBe("1");
});


it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();

    $msg->id = "5544332211";
    $msg->msgType->type = "MEDLAB";
    $msg->msgType->version = 1;
    $edifact->setMsg($msg);
    $file = $edifact->write();
    expect($file)->toContain('UNH+5544332211+MEDLAB:1');

});

it('can be validated', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/reference_id required failure/');
    $this->expectExceptionMessageMatches('/msg_type required failure/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);

});
