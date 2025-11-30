<?php

use Carbon\Carbon;
use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();
    $msg->order->observation_at = Carbon::createFromFormat("Y-m-d H:i:s", "2010-10-01 15:15:00");
    $edifact->setMsg($msg);
    expect($edifact->write())->toContain("DET:1+10:10:01+15:15'");
});

it('can get segment', function () {
    $edifact = new Edifact("DET:1+21:12:08+09:30'");
    $msg = $edifact->getMsg(new Msg());
    expect($msg->order->observation_at?->format("Y-m-d H:i"))->toBe("2021-12-08 09:30");
});

it('can throw exception on missing observation datetime', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/observation_datetime required/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});

