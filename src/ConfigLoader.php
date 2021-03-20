<?php

class ConfigLoader {

    public function get($key, $default = null) {
        $segments = explode('.', $key);
        $data = require 'config.php';

        foreach ($segments as $segment) {
            if (isset($data[$segment])) {
                $data = $data[$segment];
            } else {
                $data = $default;
                break;
            }
        }

        return $data;
    }
}