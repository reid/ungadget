<?php

require_once 'EpiCurl.php';

class Ungadget {

    private $opensocial_version = '0.8';

    public function transformFromUrl($url) {
        if (!$url) {
            throw new Exception('No URL supplied');
        }

        $hdl = $this->getCurlHandleForUrl($url);
        $ec = EpiCurl::getInstance();
        $ec->addCurl($hdl);
        $res = $ec->getResult((string) $hdl);
        $gadget = $res['data'];

        if (!$gadget) {
            throw new Exception('Request for that URL failed');
        }

        return $this->transformGadget($res['data']);
    }

    public function setOpenSocialVersion($version) {
        $this->opensocial_version = $version;
    }

    public function transformGadget($gadget) {
        $xml = new SimpleXMLElement($gadget);

        $version = $this->opensocial_version;
        if ($version) {
            $prefix = 'opensocial-';
            $required = $prefix . $version;
            $requires = $xml->xpath('/Module/ModulePrefs/Require[@feature]');
            foreach ($requires as $require) {
                $feature = $require->attributes()->feature;
                if (strpos($feature, $prefix) === 0 && $feature != $required) {
                    throw new Exception('Warning: This gadget requires '. $feature . ' instead of ' . $required . ', use ver=0.x GET param to override');
                }
            }
        }

        $contents = $xml->xpath('/Module/Content[@type="html"]');
        $content = '';

        foreach ($contents as $block) {
            $attribs = $block->attributes();
            $views = explode(',', $attribs['view']);
            if ($views[0]) {
                foreach (array('default', 'canvas') as $target) {
                    if (!in_array($target, $views)) {
                        continue 2;
                    }
                }
            }
            $content .= $block;
        }

        if ($content) {
            return $this->flatten($content);
        } else {
            $inline = $xml->xpath('/Module/Content[@type="html-inline"]');
            if ($inline) {
                return $inline[0];
            }
        }

        throw new Exception('No suitable content found');
    }

    private function flatten($html) {
        $ec = EpiCurl::getInstance();

        $hdls = array();
        foreach(array('js', 'css') as $type) {
            $hdls[$type] = array();
            $$type = '';
        }

        /* scripts */
        preg_match_all("/<script[^<>]+src=['\"]([^<>'\"]+)['\"][^<>]*>(.*)<\/script>/i", $html, $scripts);
        $html = str_replace($scripts[0], '', $html);
        foreach ($scripts[1] as $url) {
            $hdl = $this->getCurlHandleForUrl($url);
            $ec->addCurl($hdl);
            $hdls['js'][] = (string) $hdl;
        }

        /* stylesheets */
        preg_match_all("/<link[^<>]+href=['\"]([^<>'\"]+.css)['\"][^<>]*[\/]*>((<\/link>)*)/i", $html, $links); // This could do better by checking for the rel attribute...
        $html = str_replace($links[0], '', $html);
        foreach ($links[1] as $url) {
            $hdl = $this->getCurlHandleForUrl($url);
            $ec->addCurl($hdl);
            $hdls['css'][] = (string) $hdl;
        }

        /* append results */
        foreach ($hdls as $key => $ids) {
            foreach ($ids as $id) {
                $res = $ec->getResult($id);
                $res = $res['data'];
                if ($res) $$key .= $res;
            }
        }

        if ($js) $html = '<script>' . $js . '</script>' . $html;
        if ($css) $html = '<style>' . $css . '</style>' . $html;

        return $html;
    }

    private function getCurlHandleForUrl($url) {
        $hdl = curl_init();
        curl_setopt($hdl, CURLOPT_URL, $url);
        curl_setopt($hdl, CURLOPT_RETURNTRANSFER, 1);
        return $hdl;
    }

}
