<?php

namespace My\Events\Posts;

class Invitee extends Post
{
    public function getEvent()
    {
        return $this->getField('event', false);
    }

    public function setEvent($value)
    {
        return $this->updateField('event', $value);
    }

    public function getUser()
    {
        return $this->getField('user', false);
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

    public function getInvitationSent()
    {
        return $this->getField('invitation_sent');
    }

    public function setInvitationSent($value)
    {
        return $this->updateField('invitation_sent', $value);
    }
}
