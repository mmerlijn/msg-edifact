<?php

namespace mmerlijn\msgEdifact\tests\Unit\segments;

use Carbon\Carbon;
use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgEdifact\tests\TestCase;
use mmerlijn\msgRepo\Msg;

class PIDTest extends TestCase
{
    public $edifact;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->edifact = new Edifact("PID+2010:10:01+V+Vries:de:Jansen:van:A:A.++BSN123456782'");

    }

    public function test_dob_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame('2010-10-01', $msg->patient->dob->format('Y-m-d'));
        $this->assertSame('2010-10-01', $array['patient']['dob']);
    }

    public function test_dob_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();
        $msg->patient->dob = Carbon::create('2020-10-01');
        $edifact->setMsg($msg);
        $this->assertStringContainsString('PID+2020:10:01', $edifact->write());
    }

    public function test_sex_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame('F', $msg->patient->sex->value);
        $this->assertSame('F', $array['patient']['sex']);
    }

    public function test_sex_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();
        $msg->patient->dob = Carbon::create('2020-10-01');
        $msg->patient->setSex("F");
        $edifact->setMsg($msg);
        $this->assertStringContainsString('PID+2020:10:01+V', $edifact->write());
        $msg->patient->setSex("M");
        $edifact->setMsg($msg);
        $this->assertStringContainsString('PID+2020:10:01+M', $edifact->write());
    }

    public function test_name_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame('Vries', $msg->patient->name->lastname);
        $this->assertSame('de', $msg->patient->name->prefix);
        $this->assertSame('van', $msg->patient->name->own_prefix);
        $this->assertSame('Jansen', $msg->patient->name->own_lastname);
        $this->assertSame('A.', $array['patient']['name']['initials']);

        $msg = (new Edifact("PID+2010:10:01+M+Doe:de::::B++BSN123456782'"))->getMsg(new Msg());
        $this->assertSame('Doe', $msg->patient->name->own_lastname);
        $this->assertSame('de', $msg->patient->name->own_prefix);
    }

    public function test_name_setter()
    {
        //female
        $msg = new Msg();
        $edifact = new Edifact();
        $msg->patient->name->own_lastname = 'Tra';
        $msg->patient->name->own_prefix = 'van de';
        $msg->patient->name->prefix = 'van';
        $msg->patient->name->lastname = 'Groen';
        $msg->patient->name->initials = 'C';
        $msg->patient->setSex("F");
        $edifact->setMsg($msg);
        $this->assertStringContainsString('V+Groen:van:Tra:van de::C', $edifact->write());

        //male
        $msg = new Msg();
        $edifact = new Edifact();
        $msg->patient->name->own_lastname = 'Tra';
        $msg->patient->name->own_prefix = 'van de';
        $msg->patient->name->initials = 'C';
        $msg->patient->setSex("M");
        $edifact->setMsg($msg);
        $this->assertStringContainsString('M+Tra:van de::::C', $edifact->write());
    }

    public function test_bsn_getter()
    {
        $msg = $this->edifact->getMsg(new Msg());
        $array = $msg->toArray();
        $this->assertSame('123456782', $msg->patient->bsn);
        $this->assertSame('123456782', $msg->patient->ids[0]->id);
        $this->assertSame('123456782', $array['patient']['ids'][0]['id']);
    }

    public function test_bsn_setter()
    {
        $msg = new Msg();
        $edifact = new Edifact();
        $msg->patient->bsn = "123456782";
        $edifact->setMsg($msg);
        $this->assertStringContainsString('+BSN123456782', $edifact->write());
    }

    public function test_validation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/sex required/');
        $this->expectExceptionMessageMatches('/dob required/');
        $edifact = (new Edifact())->setMsg(new Msg());
        $edifact->write(true);

    }
}