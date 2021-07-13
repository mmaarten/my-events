<?php

namespace My\Events\Posts;

class Invitee extends Post
{
    public function getEvent()
    {
        return $this->getField('event');
    }

    public function setEvent($value)
    {
        return $this->updateField('event', $value);
    }

    public function getUser()
    {
        return $this->getField('user');
    }

    public function setUser($value)
    {
        return $this->updateField('user', $value);
    }

    public function getStatus()
    {
        return $this->getField('status');
    }

    public function setStatus($value)
    {
        return $this->updateField('status', $value);
    }
}
