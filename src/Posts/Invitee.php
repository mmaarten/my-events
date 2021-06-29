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
}
