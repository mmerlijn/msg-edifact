<?php

namespace mmerlijn\msgEdifact\segments;


use mmerlijn\msgRepo\Msg;

class Segment implements SegmentInterface
{
    public $repeat = false; //default segment is not repeatable

    public string $name;    //name of the segment
    public array $data;     //data of the segment (multi dimensional)

    public function __construct(public string $line = "")
    {
        $this->resetData();
        if ($line) {
            $this->setName();
            $this->lineToComponents();
        }
        return $this;
    }

    public function read(string $line): self
    {
        $this->line = $line;
        $this->setName();
        $this->lineToComponents();

        return $this;
    }

    public function write(): string
    {
        $output = "";
        foreach ($this->data as $component) {
            $output .= rtrim(implode(":", $component), ":") . "+";
        }
        return rtrim($output, "+");
    }

    public function getMsg(Msg $msg): Msg
    {
        return $msg;
    }

    public function setMsg(Msg $msg): void
    {

    }

    public function validate(): void
    {
    }

    //todo string escape
    public function setData(mixed $value, int $component, int $item = 0): self
    {
        if (!($this->data[$component][$item] ?? false)) {
            $this->expandData($component, $item);
        }
        $this->data[$component][$item] = preg_replace('/(\'|\?|\:|\+)/', '?$1', $value ?? "");
        return $this;
    }

    //todo string escape
    public function getData(int $component, int $item = 0): string
    {
        return preg_replace('/\?(\'|\?|\:|\+)/', '$1', $this->data[$component][$item] ?? "");
    }

    protected function lineToComponents()
    {
        $this->resetData();
        $components = preg_split('/(?<!\?)\\+/', $this->line);
        foreach ($components as $k => $component) {
            $this->componentsToElements($k, $component);
        }
    }

    protected function componentsToElements(int $k, string $component)
    {
        $elements = preg_split('/(?<!\?)\\:/', $component);
        $this->data[$k] = $elements;
    }

    protected function setName()
    {
        $this->name = substr($this->line, 0, 3);
    }


    protected function resetData()
    {
        $this->data = [];
        //foreach (range(0, 50) as $item) {
        //    $this->data[] = array_fill(0, 10, "");
        //}
    }

    private function expandData(int $component, int $item): void
    {
        $this->data = array_pad($this->data, $component+1,[]);
        $this->data[$component] = array_pad($this->data[$component], $item+1,"");
    }
}