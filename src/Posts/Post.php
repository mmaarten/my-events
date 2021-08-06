<?php

namespace My\Events\Posts;

class Post
{
    protected $post = null;

    public function __construct($post = null)
    {
        $this->post = get_post($post);
    }

    public function __get($key)
    {
        if (property_exists($this->post, $key)) {
            return $this->post->$key;
        }

        return null;
    }

    public function getField($selector, $format_value = true)
    {
        return get_field($selector, $this->ID, $format_value);
    }

    public function updateField($selector, $value)
    {
        return update_field($selector, $value, $this->ID);
    }

    public function deleteField($selector)
    {
        return delete_field($selector, $this->ID);
    }
}
