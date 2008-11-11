<?php

class MultiCurl {
    static $instance;
    private $hdl;
    private $requests = array();
    private $responses = array();

    function __construct() {
        $this->hdl = curl_multi_init();
    }

    public function add($hdl) {
        $key = (string) $hdl;
        $this->requests[$key] = $hdl;

        $res = curl_multi_add_handle($this->hdl, $hdl);

        if ($res === 0) {
            curl_multi_exec($this->hdl);
            return true;
        } else {
            return $res;
        }
    }

    public function get($key) {
        if (array_key_exists($key, $this->responses)) {
            return $this->responses[$key];
        }
        $active = null;
        do {
            curl_multi_exec($this->hdl, $now);
            if ($active !== null && $now != $active) {
                $this->store();
                if (array_key_exists($key, $this->responses)) {
                    return $this->responses[$key];
                }
            }
            $active = $now;
        } while ($now > 0);
        return false;
    }

    private function store() {
        while ($done = curl_multi_info_read($this->hdl)) {
            $key = (string) $done['handle'];
            $this->responses[$key] = curl_multi_getcontent($done['handle']);
            curl_multi_remove_handle($this->hdl, $done['handle']);
        }
    }

    static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MultiCurl();
        }
        return self::$instance;
    }
}

