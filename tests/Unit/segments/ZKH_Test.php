<?php

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();
    $msg->sender->setOrganisation(['name' => 'ABC']);
    $msg->sender->address = new Address(postcode: "1000AA", city: "'s Grave", street: "Race Street", building: "34a");
    $msg->sender->setPhone("1122334455");
    $edifact->setMsg($msg);
    $file =$edifact->write();
    expect($file)->toContain('ZKH+ABC+Race Street:34 a::?\'s Grave:1000AA+112 2334 455');

});

it('can get segment', function () {
    $edifact = new Edifact("ZKH+ABC+Street:10b::?'s Sands:1000BB+0123-456789'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->sender->organisation->name)->toBe('ABC')
        ->and($msg->sender->address->street)->toBe('Street')
        ->and($msg->sender->address->building)->toBe('10 b')
        ->and($msg->sender->address->building_nr)->toBe('10')
        ->and($msg->sender->address->city)->toBe("'s Sands")
        ->and($msg->sender->address->postcode)->toBe('1000BB')
        ->and((string)$msg->sender->phone)->toBe('012 3456 789');
});

it('can validate segment', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/sender_name re/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
