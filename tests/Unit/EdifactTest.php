<?php

namespace mmerlijn\msgEdifact\tests\Unit;

use mmerlijn\msgEdifact\Edifact;
use mmerlijn\msgEdifact\tests\TestCase;
use mmerlijn\msgRepo\Msg;

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
}