<?php

namespace My\Events\Posts;

class Post
{
    private $post = null;

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

    public function getMeta($key = '', $single = false)
    {
        return get_post_meta($this->ID, $key, $single);
    }

    public function addMeta($meta_key, $meta_value, $unique = false)
    {
        return add_post_meta($this->ID, $meta_key, $meta_value, $unique);
    }

    public function updateMeta($meta_key, $meta_value, $prev_value = '')
    {
        return update_post_meta($this->ID, $meta_key, $meta_value, $prev_value);
    }

    public function deleteMeta($meta_key, $meta_value = '')
    {
        return delete_post_meta($this->ID, $meta_key, $meta_value);
    }
}
