<?php

namespace mmerlijn\msgEdifact\segments;

use mmerlijn\msgRepo\Comment;
use mmerlijn\msgRepo\Msg;

class TXT extends Segment implements SegmentInterface
{
    public $repeat = true;

    public function getMsg(Msg $msg): Msg
    {
        //add comment
        $msg->addComment($this->getData(1));
        return $msg;
    }

    public function setComment(Comment $comment): self
    {
        $this->setData($comment->text, 1);

        return $this;
    }
}