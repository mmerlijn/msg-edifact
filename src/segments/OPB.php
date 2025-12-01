<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgRepo\Comment;
use mmerlijn\msgRepo\Msg;


class OPB extends Segment implements SegmentInterface
{
    public $repeat = true;

    public function getMsg(Msg $msg): Msg
    {
        //add comment
        $req_nr = count($msg->order->requests)-1;
        $obs_nr = count($msg->order->requests[$req_nr]->observations)-1;
        $msg->order->requests[$req_nr]->observations[$obs_nr]->addComment(new Comment($this->getData(1)));
        return $msg;
    }

    public function setComment(string $comment): self
    {
        $this->setData($comment, 1);

        return $this;
    }
}