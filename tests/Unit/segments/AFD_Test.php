<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;


it('can set department', function () {
    $msg = new Msg();
    $edifact = new Edifact();

    $msg->sender->setOrganisation(new \mmerlijn\msgRepo\Organisation(name: "Syntc BV",department: "inkoop"));
    $msg->sender->setPhone("0123456789");
    $edifact->setMsg($msg);
    expect($edifact->write())->toContain("AFD+inkoop+012 3456 789'");
});

it('can get department', function () {
    $msg = new Msg();
    $edifact = new Edifact("AFD+BIG COMP+0031-11223344'");

    $edifact->getMsg($msg);
    expect($msg->sender->organisation->department)->toBe("BIG COMP")
    ->and((string)$msg->sender->organisation->phone)->toBe("011 2233 44");
});

it('can validate',function(){
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/sender_organisation_department/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
