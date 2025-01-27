<?php

namespace mmerlijn\msgEdifact\tests\Unit;

use Carbon\Carbon;
use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgEdifact\tests\TestCase;
use mmerlijn\msgEdifact32\Edifact32;
use mmerlijn\msgRepo\Address;
use mmerlijn\msgRepo\Contact;
use mmerlijn\msgRepo\Enums\PatientSexEnum;
use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\Name;
use mmerlijn\msgRepo\Order;
use mmerlijn\msgRepo\Patient;
use mmerlijn\msgRepo\Phone;
use mmerlijn\msgRepo\Result;

class EdifactTest extends TestCase
{
    public function test_read_edifact_line()
    {
        $edifact = "";
        $e = (new Edifact())->read($edifact);
        $msg = $e->getMsg(new Msg());
        var_dump($msg->toArray());
        //var_dump($e);
    }

    public function test_write()
    {
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
            dt_of_observation: Carbon::now(),
        );
        $order->addResult(new Result(
            type_of_value: 'TV', value: '*', test_code: 'FUND', test_name: 'FUND'
        ));
        $order->addResult(new Result(
            type_of_value: 'CV', value: '386', test_code: 'FSFUFZ', test_name: 'advFUfund'
        ));
        $repo->setOrder($order);
        $repo->addComment("Dit is een comment over een patiënt");
        $vrij = new Edifact();
        $vrij->setMsg($repo);
        $vrij->setSegmentValue('UNB', 0, '011212121', 2);
        $vrij->setSegmentValue('UNB', 0, '011212121', 3);
        $output = $vrij->write();
        $this->assertStringContainsString("UNB+UNOA:",  $output); //phone number is nb
        $this->assertStringContainsString("PID+2000:01:01+M+Groot:de::::J++BSN123456782'",  $output);
        $this->assertStringContainsString("PAD+Straatnaam:1 a::Stad:1234AB'",  $output);
        $this->assertStringContainsString("TXT:1+Dit is een comment over een patiënt",  $output);
        $this->assertStringContainsString("UNZ",  $output);
    }
}