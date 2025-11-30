<?php

use Carbon\Carbon;
use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();

    $msg->sender->agbcode = "90001122";
    $msg->receiver->agbcode = "90001133";
    $msg->datetime = Carbon::create("2020-10-11 14:00:00");
    $msg->processing_id = "ad12345678";
    $edifact->setMsg($msg);
    $file = $edifact->write();
    expect($file)->toContain('UNB+UNOA:1+90001122+90001133+201011:1400+ad12345678');
});

it('can get segment', function () {
    $edifact = new Edifact("UNB+UNOA:1+50001234+50004321+211011:1530+ad12345678'");
    $msg = $edifact->getMsg(new Msg());
    $array = $msg->toArray();
    expect($msg->sender->agbcode)->toBe("50001234")
        ->and($array['sender']['agbcode'])->toBe("50001234")
        ->and($msg->receiver->agbcode)->toBe("50004321")
        ->and($array['receiver']['agbcode'])->toBe("50004321")
        ->and($msg->datetime->format("Y-m-d H:i:s"))->toBe("2021-10-11 15:30:00")
        ->and($msg->processing_id)->toBe("ad12345678")
        ->and($array['processing_id'])->toBe("ad12345678");
});

it('can validate segment', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/sender_agbcode required/');
    $this->expectExceptionMessageMatches('/receiver_agbcode required/');
    $this->expectExceptionMessageMatches('/processing_id required/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
