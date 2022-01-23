<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgRepo\Msg;


class OPB extends Segment implements SegmentInterface
{
    public $repeat = true;

    public function getMsg(Msg $msg): Msg
    {
        //add comment
        $msg->order->results[count($msg->order->results) - 1]->addComment($this->getData(1));
        return $msg;
    }

    public function setComment(string $comment): self
    {
        $this->setData($comment, 1);

        return $this;
    }
}