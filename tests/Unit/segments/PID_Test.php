<?php

use Carbon\Carbon;
use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Msg;

it('can set segment', function () {
    $msg = new Msg();
    $edifact = new Edifact();
    $msg->patient->dob = Carbon::create('2020-10-01');
    $msg->patient->setSex("F");
    $msg->patient->name->own_lastname = 'Tra';
    $msg->patient->name->own_prefix = 'van de';
    $msg->patient->name->prefix = 'van';
    $msg->patient->name->lastname = 'Groen';
    $msg->patient->name->initials = 'C';
    $msg->patient->bsn = "123456782";
    $edifact->setMsg($msg);
    $file = $edifact->write();
    expect($file)->toContain('PID+2020:10:01+V+Groen:van:Tra:van de::C')
    ->and($file)->toContain('+BSN123456782');
    $msg->patient->setSex("M");
    $edifact->setMsg($msg);
    expect($edifact->write())->toContain('PID+2020:10:01+M');
});

it('can get segment', function () {
    $edifact = new Edifact("PID+2010:10:01+V+Vries:de:Jansen:van:A:A.++BSN123456782'");
    $msg = $edifact->getMsg(new Msg());
    $array = $msg->toArray();
    expect($msg->patient->dob->format('Y-m-d'))->toBe('2010-10-01')
        ->and($array['patient']['dob'])->toBe('2010-10-01')
        ->and($msg->patient->sex->value)->toBe('F')
        ->and($array['patient']['sex'])->toBe('F')
        ->and($msg->patient->name->lastname)->toBe('Vries')
        ->and($msg->patient->name->prefix)->toBe('de')
        ->and($msg->patient->name->own_prefix)->toBe('van')
        ->and($msg->patient->name->own_lastname)->toBe('Jansen')
        ->and($array['patient']['name']['initials'])->toBe('A.')
        ->and($msg->patient->bsn)->toBe('123456782')
        ->and($msg->patient->ids[0]->id)->toBe('123456782')
        ->and($array['patient']['ids'][0]['id'])->toBe('123456782');
});

it('can validate segment', function () {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessageMatches('/sex required/');
    $this->expectExceptionMessageMatches('/dob required/');
    $edifact = (new Edifact())->setMsg(new Msg());
    $edifact->write(true);
});
