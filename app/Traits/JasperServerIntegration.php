<?php

namespace App\Traits;

use Exception;

class JasperServerIntegration
{
    private $url;
    private $reportPath;
    private $format;
    private $username;
    private $password;
    private $params;

    public function __construct($url, $reportPath, $format, $username, $password, $params = [])
    {
        $this->url = $url;
        $this->reportPath = $reportPath;
        $this->format = $format;
        $this->username = $username;
        $this->password = $password;
        $this->params = $params;
    }

    private function getQueryString()
    {
        $queryString = "";
        // Loop through the parameters and encode them properly
        foreach ($this->params as $key => $val) {
            $queryString .= '&' . $key . '=' . urlencode($val);
        }
        return $queryString;
    }

    public function execute()
    {
        $url = $this->url . '/rest_v2/reports' . $this->reportPath . '.' . $this->format . '?';

        $queryString = $this->getQueryString();
        $url .= substr($queryString, 1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90000); // Timeout after 30 seconds
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
