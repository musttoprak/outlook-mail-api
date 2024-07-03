<?php

class EmailMessage
{
    public $subject;
    public $from;
    public $to;
    public $date;
    public $message_id;
    public $size;
    public $uid;
    public $msgno;
    public $recent;
    public $flagged;
    public $answered;
    public $deleted;
    public $seen;
    public $draft;
    public $udate;
    public $body;

    public function __construct($overview, $body)
    {
        $this->subject = $overview->subject;
        $this->from = $overview->from;
        $this->to = $overview->to;
        $this->date = $overview->date;
        $this->message_id = $overview->message_id;
        $this->size = $overview->size;
        $this->uid = $overview->uid;
        $this->msgno = $overview->msgno;
        $this->recent = $overview->recent;
        $this->flagged = $overview->flagged;
        $this->answered = $overview->answered;
        $this->deleted = $overview->deleted;
        $this->seen = $overview->seen;
        $this->draft = $overview->draft;
        $this->udate = $overview->udate;
        $this->body = $body;
    }
}
