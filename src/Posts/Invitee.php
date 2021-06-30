<?php

namespace My\Events\Posts;

class Invitee extends Post
{
    /**
     * Get user
     *
     * @return int
     */
    public function getUser()
    {
        return $this->getField('user');
    }

    /**
     * Set user
     *
     * @param int $value
     * @return bool
     */
    public function setUser($value)
    {
        return $this->updateField('user', $value);
    }

    /**
     * Get event
     *
     * @return int
     */
    public function getEvent()
    {
        return $this->getField('event');
    }

    /**
     * Set event
     *
     * @param int $value
     * @return bool
     */
    public function setEvent($value)
    {
        return $this->updateField('event', $value);
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getField('status');
    }

    /**
     * Set status
     *
     * @param string $value
     * @return bool
     */
    public function setStatus($value)
    {
        return $this->updateField('status', $value);
    }

    /**
     * Get status
     *
     * @return bool
     */
    public function getEmailSent()
    {
        return $this->getField('email_sent') ? true : false;
    }

    /**
     * Set status
     *
     * @param bool $value
     * @return bool
     */
    public function setEmailSent($value)
    {
        return $this->updateField('email_sent', $value);
    }
}
