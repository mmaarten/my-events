<?php

namespace My\Events\Posts;

class Invitee extends Post
{
    public function getEvent()
    {
        return $this->getMeta('event', true);
    }

    public function setEvent($value)
    {
        return $this->updateMeta('event', $value);
    }

    public function getUser()
    {
        return $this->getMeta('user', true);
    }

    public function setUser($value)
    {
        return $this->updateMeta('user', $value);
    }

    public function getStatus()
    {
        return $this->getMeta('status', true);
    }

    public function setStatus($value)
    {
        return $this->updateMeta('status', $value);
    }
}
