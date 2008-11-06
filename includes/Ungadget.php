<?php

require_once 'EpiCurl.php';

class Ungadget {

    private $opensocial_version = '0.8';
    private $strip_newlines = true;

    public function transformFromUrl($url) {
        $hdl = $this->getCurlHandleForUrl($url);
        $ec = EpiCurl::getInstance();
        $ec->addCurl($hdl);
        $gadget = $ec->getResult((string)$hdl);
        return $this->transformGadget($gadget['data']);
    }

    public function setOpenSocialVersion($version) {
        $this->opensocial_version = $version;
    }

    public function setStripNewlines($bool) {
        $this->strip_newlines = $bool;
    }

    public function transformGadget($gadget) {
        $xml = new SimpleXMLElement($gadget);

        if (!$xml) throw new Exception('Bad XML');

        $version = $this->opensocial_version;
        if ($version) {
            $opensocial = $xml->xpath('/Module/ModulePrefs/Require[@feature="opensocial-' . $version . '"]');
            if (count($opensocial) == 0) {
                throw new Exception('This gadget does not support OpenSocial ' . $version);
            }
        }

        $content = $xml->xpath('/Module/Content[@type="html"]');
        if ($content) {
            $ec = EpiCurl::getInstance();

            /* scripts */
            $content = $content[0];
            $script_tags = preg_match_all("/<script[^<>]+src=['\"]([^<>'\"]+)['\"][^<>]*>(.*)<\/script>/i", $content, $script_matches);
            $script_keys = array();
            foreach ($script_matches[1] as $url) {
                $hdl = $this->getCurlHandleForUrl($url);
                $ec->addCurl($hdl);
                $script_keys[] = (string) $hdl;
            }
            $content = str_replace($script_matches[0], '', $content);

            /* stylesheets */
            $link_tags = preg_match_all("/<link[^<>]+href=['\"]([^<>'\"]+.css)['\"][^<>]*[\/]*>((<\/link>)*)/i", $content, $link_matches); // This could do better by checking for the rel attribute...
            $stylesheet_keys = array();
            foreach ($link_matches[1] as $url) {
                $hdl = $this->getCurlHandleForUrl($url);
                $ec->addCurl($hdl);
                $stylesheet_keys[] = (string) $hdl;
            }
            $content = str_replace($link_matches[0], '', $content);

            /* append results */
            $js = '';
            $style = '';
            foreach ($script_keys as $key) {
                $res = $ec->getResult($key);
                $res = $res['data'];
                if ($res) $js .= $res;
            }
            foreach ($stylesheet_keys as $key) {
                $res = $ec->getResult($key);
                $res = $res['data'];
                if ($res) $style .= $res;
            }
            if ($js) $content = '<script>' . $js . '</script>' . $content;
            if ($content) $content = '<style>' . $style . '</style>' . $content;

            if ($this->strip_newlines) $content = str_replace("\n", '', $content);

            return $content;
        } else {
            $inline = $xml->xpath('/Module/Content[@type="html-inline"]');
            if ($inline) {
                return $inline[0];
            } else {
                throw new Exception('Content not found');
            }
        }
    }

    private function getCurlHandleForUrl($url) {
        $hdl = curl_init();
        curl_setopt($hdl, CURLOPT_URL, $url);
        curl_setopt($hdl, CURLOPT_RETURNTRANSFER, 1);
        return $hdl;
    }

}
