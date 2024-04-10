<?php

namespace mmerlijn\msgEdifact;

use mmerlijn\msgEdifact\segments\AFD;
use mmerlijn\msgEdifact\segments\ARA;
use mmerlijn\msgEdifact\segments\ART;
use mmerlijn\msgEdifact\segments\BEP;
use mmerlijn\msgEdifact\segments\DET;
use mmerlijn\msgEdifact\segments\GGA;
use mmerlijn\msgEdifact\segments\GGO;
use mmerlijn\msgEdifact\segments\IDE;
use mmerlijn\msgEdifact\segments\NUB;
use mmerlijn\msgEdifact\segments\OPB;
use mmerlijn\msgEdifact\segments\PAD;
use mmerlijn\msgEdifact\segments\PID;
use mmerlijn\msgEdifact\segments\TXT;
use mmerlijn\msgEdifact\segments\UNB;
use mmerlijn\msgEdifact\segments\Undefined;
use mmerlijn\msgEdifact\segments\UNH;
use mmerlijn\msgEdifact\segments\UNT;
use mmerlijn\msgEdifact\segments\UNZ;
use mmerlijn\msgEdifact\segments\ZKH;
use mmerlijn\msgEdifact\validation\Validator;
use mmerlijn\msgRepo\Msg;

class Edifact
{
    private string $msg = "";
    public string $type = "MEDLAB";
    public array $segments = [];

    public function __construct(string $edifact = "")
    {
        if ($edifact) {
            $this->msg = $edifact;
            $this->buildSegments();
        }

        return $this;
    }

    public function read(string $edifact): self
    {
        $this->msg = $edifact;
        $this->buildSegments();
        return $this;
    }

    public function write(bool $validate = false): string
    {
        Validator::reset();
        $output = "";
        foreach ($this->segments as $teller => $segment) {
            if ($validate)
                $segment->validate();
            if ($segment->name == "UNT")
                $segment->setData($teller, 1);
            $output .= $segment->write() . "'" . chr(13);
        }
        if (Validator::fails()) {
            throw new \Exception("Edifact validation fails: " . PHP_EOL . implode(PHP_EOL, Validator::getErrors()));
        }
        return $output;
    }

    public function getMsg(Msg $msg): Msg
    {
        foreach ($this->segments as $segment) {
            $msg = $segment->getMsg($msg);
        }
        return $msg;
    }

    public function setMsg(Msg $msg): self
    {
        $this->type = $msg->msgType->type ?: "MEDLAB";

        if (empty($this->segments)) {
            $this->createDefaultSegments();
        }
        foreach ($this->segments as $k => $segment) {
            $this->segments[$k]->setMsg($msg);
        }
        //set results
        if (!empty($msg->order->results)) {
            $teller_BEP = 1;
            $teller_NUB = 1;
            foreach ($msg->order->results as $k => $result) {
                if ($result->done) {
                    array_splice($this->segments, $this->findSegmentKey("IDE") + 1, 0, [(new BEP("BEP:1:1:$teller_BEP"))->setResult($result)]);
                    $teller_OPB = 1;
                    foreach ($result->comments as $comment) {
                        array_splice($this->segments, $this->findSegmentKey("BEP") + 1, 0, [(new OPB("OPB:1:1:$teller_BEP:$teller_OPB"))->setComment($comment)]);
                        $teller_OPB++;
                    }
                    $teller_BEP++;
                } else {
                    array_splice($this->segments, $this->findSegmentKey("UNT"), 0, [(new NUB("NUB:1:$teller_NUB+"))->setResult($result)]);
                    $teller_NUB++;
                }
            }
        }
        //zet comments
        if (!empty($msg->comments)) {
            $teller_TXT = 1;
            foreach ($msg->comments as $comment) {
                array_splice($this->segments, $this->findSegmentKey("GGO"), 0, [(new TXT("TXT:$teller_TXT"))->setComment($comment)]);
                $teller_TXT++;
            }
        }
        return $this;
    }
    //helper function to set specific segment values
    public function setSegmentValue(string $SEG, string $value, int $component, int $item = 0): self
    {
        $key = $this->findSegmentKey($SEG);
        if ($key < count($this->segments)) {
            $this->segments[$key]->setData($value, $component, $item);
        }
        return $this;
    }

    //search for first segment occurrence
    public function findSegmentKey(string $SEG): int|string
    {
        foreach ($this->segments as $k => $segment) {
            if ($segment->name == $SEG) {
                return $k;
            }
        }
        return count($this->segments);
    }

    protected function buildSegments(): void
    {
        $this->segments = [];
        $lines = preg_split("/(?<!\?)'/", trim($this->msg));
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line)) {
                $segment = 'mmerlijn\\msgEdifact\\segments\\' . substr($line, 0, 3);
                if (class_exists($segment)) {
                    $this->segments[] = new $segment($line);
                } else {
                    $this->segments[] = new Undefined($line);
                }
            }
        }
    }

    //MEDLAB
    protected function createDefaultSegments()
    {
        if ($this->type == "MEDLAB") {
            $this->segments = [
                new UNB("UNB+UNOA:1++++"),
                new UNH("UNH++MEDLAB:1"),
                new ZKH("ZKH++::::+"),
                new PID("PID+++:::::++"),
                new PAD("PAD+::::+"),
                new ART("ART+H++::::+"),
                new AFD("AFD++"),
                new ARA("ARA:1++"),
                new DET("DET:1+::+:"),
                new IDE("IDE:1++"),
                new UNT("UNT++"),
                new UNZ("UNZ+1+"),
            ];
        } elseif ($this->type == "MEDVRI") {
            $this->segments = [
                new UNB("UNB+UNOA:1++++"),
                new UNH("UNH++MEDVRI:1"),
                new GGA("GGA++++::::+"),
                new DET("DET:1+::+:"),
                new PID("PID+++:::::++"),
                new PAD("PAD+::::+"),
                new GGO("GGO++++::::+"),
                new UNT("UNT++"),
                new UNZ("UNZ+1+"),
            ];
        }
    }
}