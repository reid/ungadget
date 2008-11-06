<?php

require_once 'EpiCurl.php';

class Ungadget {

    private $opensocial_version = '0.8';
    private $strip_newlines = true;

    public function transformFromUrl($url) {
        $hdl = $this->getCurlHandleForUrl($url);
        $ec = EpiCurl::getInstance();
        $ec->addCurl($hdl);
        $gadget = $ec->getResult((string) $hdl);
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
            if (count($opensocial) == 0)
                throw new Exception('This gadget does not support OpenSocial ' . $version);
        }

        $contents = $xml->xpath('/Module/Content[@type="html"]');
        $content = '';
        foreach ($contents as $block) {
            $content .= $block;
        }

        if ($content) {
            $ec = EpiCurl::getInstance();

            $hdls = array();
            foreach(array('js', 'css') as $type) $hdls[$type] = array();

            /* scripts */
            preg_match_all("/<script[^<>]+src=['\"]([^<>'\"]+)['\"][^<>]*>(.*)<\/script>/i", $content, $scripts);
            $content = str_replace($scripts[0], '', $content);
            foreach ($scripts[1] as $url) {
                $hdl = $this->getCurlHandleForUrl($url);
                $ec->addCurl($hdl);
                $hdls['js'][] = (string) $hdl;
            }

            /* stylesheets */
            preg_match_all("/<link[^<>]+href=['\"]([^<>'\"]+.css)['\"][^<>]*[\/]*>((<\/link>)*)/i", $content, $links); // This could do better by checking for the rel attribute...
            $content = str_replace($links[0], '', $content);
            foreach ($links[1] as $url) {
                $hdl = $this->getCurlHandleForUrl($url);
                $ec->addCurl($hdl);
                $hdls['css'][] = (string) $hdl;
            }

            /* append results */
            $js = $css = '';

            foreach ($hdls as $key => $ids) {
                foreach ($ids as $id) {
                    $res = $ec->getResult($id);
                    $res = $res['data'];
                    if ($res) $$key .= $res;
                }
            }

            if ($js) $content = '<script>' . $js . '</script>' . $content;
            if ($css) $content = '<style>' . $css . '</style>' . $content;
            if ($this->strip_newlines) $content = str_replace("\n", '', $content);

            return $content;
        } else {
            $inline = $xml->xpath('/Module/Content[@type="html-inline"]');
            if ($inline)
                return $inline[0];
            else
                throw new Exception('Content not found');
        }
    }

    private function getCurlHandleForUrl($url) {
        $hdl = curl_init();
        curl_setopt($hdl, CURLOPT_URL, $url);
        curl_setopt($hdl, CURLOPT_RETURNTRANSFER, 1);
        return $hdl;
    }

}
