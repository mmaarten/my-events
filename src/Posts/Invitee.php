<?php

namespace My\Events\Posts;

class Invitee extends Post
{
    /**
     * Get event
     *
     * @return int
     */
    public function getEvent()
    {
        return $this->getMeta('event', true);
    }

    /**
     * Set event
     *
     * @param int $value
     * @return bool
     */
    public function setEvent($value)
    {
        return $this->updateMeta('event', $value);
    }

    /**
     * Get user
     *
     * @return int
     */
    public function getUser()
    {
        return $this->getMeta('user', true);
    }

    /**
     * Set user
     *
     * @param int $value
     * @return bool
     */
    public function setUser($value)
    {
        return $this->updateMeta('user', $value);
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getMeta('status', true);
    }

    /**
     * Set status
     *
     * @param string $value
     * @return bool
     */
    public function setStatus($value)
    {
        return $this->updateMeta('status', $value);
    }

    /**
     * Email sent
     *
     * @return bool
     */
    public function isEmailSent()
    {
        return $this->getMeta('email_sent') ? true : false;
    }

    /**
     * Set email sent
     *
     * @param bool $value
     * @return bool
     */
    public function setEmailSent($value)
    {
        return $this->updateMeta('email_sent', $value);
    }
}
