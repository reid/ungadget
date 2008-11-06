<?php

class Ungadget {

    static $opensocial_version = '0.8';

    static function fromUrl($url) {
        $gadget = self::makeRequest($url);
        return self::flatten($gadget);
    }

    static function makeRequest($url) {
        $hdl = curl_init();
        curl_setopt($hdl, CURLOPT_URL, $url);
        curl_setopt($hdl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($hdl);
        curl_close($hdl);
        return $res;
    }

    static function flatten($gadget) {
        $xml = new SimpleXMLElement($gadget);

        if (!$xml) throw new Exception('Bad XML');

        if ($xml->xpath('/Module/ModulePrefs/require[@feature="opensocial-' . self::$opensocial_version . '"]') === false) {
            throw new Exception('Missing required version');
        }

        $content = $xml->xpath('/Module/Content[@type="html"]');

        if ($content) {
            // TODO consolidate scripts, styles
            return $content[0];
        } else {
            $inline = $xml->xpath('/Module/Content[@type="html-inline"]');
            if ($inline) {
                return $inline[0];
            } else {
                throw new Exception('Content not found');
            }
        }
    }

}

echo Ungadget::fromUrl($_GET['url']);
