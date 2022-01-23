<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgRepo\Msg;
use mmerlijn\msgRepo\OrderItem;

class TXT extends Segment implements SegmentInterface
{
    public $repeat = true;

    public function getMsg(Msg $msg): Msg
    {
        //add comment
        $msg->addComment($this->getData(1));
        return $msg;
    }

    public function setComment(string $comment): self
    {
        $this->setData($comment, 1);

        return $this;
    }
}