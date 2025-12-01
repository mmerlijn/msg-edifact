<?php

use Carbon\Carbon;
use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Contact;
use mmerlijn\msgRepo\Enums\PatientSexEnum;
use mmerlijn\msgRepo\Enums\ValueTypeEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Order;
use mmerlijn\msgRepo\Patient;
use mmerlijn\msgRepo\Phone;
use mmerlijn\msgRepo\TestCode;

it('can write edifact MEDLAB', function () {
    $repo = new Msg();
    $repo->processing_id = 'T12345678';
    $repo->id = '12345678';
    $repo->setPatient(new Patient(sex: PatientSexEnum::MALE, name: new Name(initials: 'J', lastname: 'Jansen', prefix: 'van', own_lastname: 'Groot', own_prefix: 'de'), dob: Carbon::create('2000-01-01'), bsn: '123456782', address: new Address(postcode: '1234AB', city: 'Stad', street: 'Straatnaam', building: '1a'), phones: [new Phone(number: 'nb')]));
    $repo->setReceiver(new Contact(agbcode: '012345678', name: new Name(initials: 'P',own_lastname: 'Huisarts'), source: 'VEKTIS', address: new Address(postcode: '9988AB', city: 'City', street: 'Street', building: '1a')));
    $repo->setSender(new Contact(agbcode: '011212121', name: new Name(own_lastname: 'Salt'), address: new Address(postcode: "1040AA", city: 'Amsterdam', street: 'HStreet', building: "2b"), phone: '0612345678'));
    $order = new Order(
        request_nr: 'ZD12345678',
        lab_nr: '012345',
        requester: new Contact(agbcode: '012345678', name: new Name(initials: 'P',own_lastname: 'Huisarts'), source: 'VEKTIS', address: new Address(postcode: '9988AB', city: 'City', street: 'Street', building: '1a')),
        observation_at: Carbon::now(),
    );
    $order->addObservation(new \mmerlijn\msgRepo\Observation(
        type: ValueTypeEnum::ST, value: '*', test: new TestCode(code: 'FUND', value: 'FUND')
    ));
    $order->addObservation(new \mmerlijn\msgRepo\Observation(
        type: ValueTypeEnum::ST, value: '386', test: new TestCode(code: 'FSFUFZ', value: 'advFUfund')
    ));
    $repo->setOrder($order);
    $repo->addComment("Dit is een comment over een patiënt");
    $vrij = new Edifact();
    $vrij->setMsg($repo);
    expect($vrij->write())->toContain("UNB+UNOA:") //phone number is nb
    ->toContain("PID+2000:01:01+M+Groot:de::::J++BSN123456782'")
    ->toContain("PAD+Straatnaam:1 a::Stad:1234AB'")
    ->not->toContain("TXT:1+Dit is een comment over een patiënt")
    ->toContain("UNZ");
});

it('can write edifact MEDVRI', function () {
    $repo = new Msg();
    $repo->processing_id = 'T12345678';
    $repo->msgType->type = 'MEDVRI';
    $repo->id = '12345678';
    $repo->setPatient(new Patient(sex: PatientSexEnum::MALE, name: new Name(initials: 'J', lastname: 'Jansen', prefix: 'van', own_lastname: 'Groot', own_prefix: 'de'), dob: Carbon::create('2000-01-01'), bsn: '123456782', address: new Address(postcode: '1234AB', city: 'Stad', street: 'Straatnaam', building: '1a'), phones: [new Phone(number: 'nb')]));
    $repo->setReceiver(new Contact(agbcode: '012345678', name: new Name(initials: 'P',own_lastname: 'Huisarts'), source: 'VEKTIS', address: new Address(postcode: '9988AB', city: 'City', street: 'Street', building: '1a')));
    $repo->setSender(new Contact(agbcode: '011212121', name: new Name(own_lastname: 'Salt'), address: new Address(postcode: "1040AA", city: 'Amsterdam', street: 'HStreet', building: "2b"), phone: '0612345678'));
    $repo->addComment("Dit is een hele lange tekst zodat deze niet in één regel past en dus opgesplitst moet worden in meerdere TXT segmenten. Dit is belangrijk om te testen of de opsplitsing goed werkt in de Edifact writer.");
    $vrij = new Edifact();
    $vrij->setMsg($repo);
    expect($vrij->write())->toContain("UNB+UNOA:") //phone number is nb
    ->toContain("PID+2000:01:01+M+Groot:de::::J++BSN123456782'")
        ->toContain("PAD+Straatnaam:1 a::Stad:1234AB'")
        ->toContain("TXT:1+Dit is een hele lange tekst zodat deze niet in één regel past en")
        ->toContain("TXT:2+dus opgesplitst moet worden in meerdere TXT segmenten. Dit is")
        ->toContain("TXT:3+belangrijk om te testen of de opsplitsing goed werkt in de Edifact")
        ->toContain("TXT:4+writer.'".chr(13)."GGO")
        ->toContain("UNZ");
});

