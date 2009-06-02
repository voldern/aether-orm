<?php // 
/**
 * 
 * A simple wrapper for curl that sets a few sane defaults and wraps
 * response headers together with response.
 * 
 * Created: 2009-05-29
 * @author Simen Graaten
 * @package pg2
 */

class URLDownloader {
    function __construct($options = array()) {
        $this->ch = curl_init();

        // Set default options
        if (!isset($options[CURLOPT_HEADER])) 
            $options[CURLOPT_HEADER] = false;
        if (!isset($options[CURLOPT_FOLLOWLOCATION])) 
            $options[CURLOPT_FOLLOWLOCATION] = true;
        if (!isset($options[CURLOPT_MAXREDIRS])) 
            $options[CURLOPT_MAXREDIRS] = 2;
        if (!isset($options[CURLOPT_FILETIME])) 
            $options[CURLOPT_FILETIME] = true;
        if (!isset($options[CURLOPT_RETURNTRANSFER])) 
            $options[CURLOPT_RETURNTRANSFER] = true;
        if (!isset($options[CURLOPT_CONNECTTIMEOUT])) 
            $options[CURLOPT_CONNECTTIMEOUT] = 15;

        curl_setopt_array($this->ch, $options);
    }

    /**
     * Fetched the URL and returns the response or an error
     *
     * @param string $url
     * @return array
     */
    public function fetch($url) {
        curl_setopt($this->ch, CURLOPT_URL, $url);

        $data = curl_exec($this->ch);
        if (curl_errno($this->ch)) {
            return array(
                'error' => array(
                    'id' => curl_errno($this->ch), 
                    'message' => curl_error($this->ch)
                )
            );
        }

        $http = array(
            'httpCode' => curl_getinfo($this->ch, CURLINFO_HTTP_CODE),
            'sizeDownload' => curl_getinfo($this->ch, CURLINFO_SIZE_DOWNLOAD),
            'speedDownload' => curl_getinfo($this->ch, CURLINFO_SPEED_DOWNLOAD),
            'response' => $data
        );

        return $http;
    }
}

?>
