<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Phone;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();

    $msg->sender->name->name = "Big Doe";
    $msg->sender->setPhone(new Phone("0123456789"));
    $edifact->setMsg($msg);
    $file = $edifact->write();
    expect($file)->toContain("ARA:1+Big Doe+012 3456 789'");
});

it('can get segment', function () {
    $edifact = new Edifact("ARA:1+John Doe+0223344555'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->sender->name->name)->toBe("John Doe")
        ->and((string)$msg->sender->phone)->toBe("0223 344 555");
});

it('can validate segment', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/sender_name required/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
