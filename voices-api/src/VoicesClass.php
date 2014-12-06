<?php

require_once('Voices/Exceptions.php');

class Voices
{

    public $apikey;
    public $ch;
    public $endpoint  = 'https://api.voices.com/v4/partners/';
    public $debug = false;
    // always access the top level data property on response
    public $auto_data = true;

    /**
     * Create a new Voices Instance
     */
    public function __construct($apikey = null, $opts = array())
    {
        $this->apikey = $apikey;
        if (isset($opts['debug'])){
            $this->debug = true;
        }

        if (isset($opts['auto_data'])){
            $this->auto_data = $opts['auto_data'];
        }

        if (!isset($opts['timeout']) || !is_int($opts['timeout'])){
            $opts['timeout'] = 600;
        }

        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Voices-PHP/1.0.0');
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $opts['timeout']);
    }

    public function __destruct() {
        if(is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

    public function call($url, $params = null) {
        $ch     = $this->ch;
        curl_setopt($ch, CURLOPT_URL, $this->endpoint . $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-API-KEY: '.$this->apikey,
            'TEST-MODE: '.(int)$this->debug
            ));
        // handle post requests
        if (is_array($params)) {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

        $start = microtime(true);
        $this->log('Call to ' . $this->endpoint . $url);
        if($this->debug) {
            $curl_buffer = fopen('php://memory', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $curl_buffer);
        }

        $response_body = curl_exec($ch);

        $info = curl_getinfo($ch);
        $time = microtime(true) - $start;
        if($this->debug) {
            rewind($curl_buffer);
            $this->log(stream_get_contents($curl_buffer));
            fclose($curl_buffer);
        }
        $this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
        $this->log('Got response: ' . $response_body);

        if(curl_error($ch)) {
            throw new Voices_Error("API call to $url failed: " . curl_error($ch));
        }
        $result = json_decode($response_body);
        if (isset($response_body->error)) {
            throw new Voices_Error("API call to $url failed. Code: " . $response_body->error->code . ". Message: ". $response_body->error->message);
        }
        if (isset($response_body->notices)) {
            $this->log("Notice Warning. Code: " . $response_body->notices->code . ". Message: ". $response_body->notices->message);
        }

        if(floor($info['http_code'] / 100) >= 4) {
            throw new Voices_Error('We received an unexpected error: ' . $result);
        }
        if ($this->auto_data) {
            return $result->data;
        } else {
            return $result;
        }
    }

    public function log($msg) {
        if ($this->debug) {
            error_log($msg);
        }
    }

    public function get_programs()
    {
        return $this->call('programs');
    }

    public function get_program($id = null)
    {
        if (is_null($id)) {
            throw new Voices_Error("Program ID must not be null.");
            return false;
        }
        return $this->call('programs/'.$id);
    }

    public function get_jobs($id = null)
    {
        if (is_null($id)) {
            throw new Voices_Error("Program ID must not be null.");
            return false;
        }
        return $this->call('programs/'.$id.'/jobs');
    }

    public function get_job($id = null)
    {
        if (is_null($id)) {
            throw new Voices_Error("Job ID must not be null.");
            return false;
        }
        return $this->call('jobs/'.$id);
    }

    public function get_talents($id = null)
    {
        if (is_null($id)) {
            throw new Voices_Error("Program ID must not be null.");
            return false;
        }
        return $this->call('programs/'.$id.'/talents');
    }

    public function get_talent($id = null)
    {
        if (is_null($id)) {
            throw new Voices_Error("Talent ID must not be null.");
            return false;
        }
        return $this->call('talents/'.$id);
    }
    // untested
    public function create_job($program_id = null, $talent_id = null, $script = null)
    {
        if (is_null($program_id)) {
            throw new Voices_Error("Program ID must not be null.");
            return false;
        }
        if (is_null($talent_id)) {
            throw new Voices_Error("Talent ID must not be null.");
            return false;
        }
        if (!is_file($script)) {
            throw new Voices_Error("Script must be a valid file.");
            return false;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $script);
        $script_file = curl_file_create($script, $mime, basename($script));
        return $this->call('jobs', array(
            'program_id' => $program_id,
            'talent_id' => $talent_id,
            'script' => $script
            ));
    }
}
