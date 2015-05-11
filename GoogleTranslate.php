<?php

/**
 * Google-Translate-API
 * New API Library for Google translate V2 in PHP
 * @link https://Github.com/korra88/Google-Translate-API
 * @license http://Www.gnu.org/copyleft/gpl.html
 * @version 1.1
 * @author Vinicius Gava (gava.vinicius@gmail.com)
 * @author Emanuele Corradini (korra88@gmail.com)
 */
class GoogleTranslate {

    /**
     * URI API
     * @var string
     */
    private $apiUri = 'https://www.googleapis.com/language/translate/v2';

    /**
     * Access Key to API
     * @var string
     */
    private $accessKey = '';

    /**
     *
     * @var Curl
     */
    private $connect;

    /**
     * list parameters used in get request
     * @var array
     */
    private $parameters = array();

    /**
     * Service translate text
     */
    CONST SERVICE_TRANSLATE = 'translate';

    /**
     * Service detect language
     */
    CONST SERVICE_DETECT = 'detect';

    /**
     * Service language support
     */
    CONST SERVICE_LANGUAGE = 'language';

    /**
     * Text is html. Used in "translate" method.
     */
    CONST HTML_FORMAT = 'html';

    /**
     * Text is plaintext.USed in "translate" method.
     */
    CONST PLAINTEXT_FORMAT = 'text';

    public function __construct($accessKey) {
        $this->setAccessKey($accessKey);
    }

    /**
     * Set access key
     * @param string $key
     */
    public function setAccessKey($key) {
        if (strlen($key) == 39) {
            $this->accessKey = $key;
        } else {
            throw new GoogleTranslateException("Invalid Access Key", 1);
        }
    }

    /**
     * Translate text
     * @param string|array $text The text to be translated
     * @param string $targetLanguage The language to translate the source text into
     * @param string|null|array $sourceLanguage The language of the source text. If a language is not specified, the system will attempt to identify the source language automatically
     * @param string $format    Format of the text to translate: 'html' or 'text'. Default to 'html'.
     * @return text|array|false
     */
    public function translate($text, $targetLanguage, &$sourceLanguage = null, $format = self::HTML_FORMAT) {
        if ($this->isValid($text, $targetLanguage, $sourceLanguage)) {
            // Add keyAccess
            $this->addQueryParam('key', $this->accessKey);
            // Add text to be translate
            $this->addQueryParam('q', $text);
            // Add target language
            $this->addQueryParam('target', $targetLanguage);
            // If source not null, add param to query
            if (!is_null($sourceLanguage)) {
                $this->addQueryParam('source', $sourceLanguage);
            }
            // Text format, defaults to html
            switch ($format) {
                default:
                    $format = self::HTML_FORMAT;
                case self::HTML_FORMAT:
                case self::PLAINTEXT_FORMAT:
                    $this->addQueryParam('format', $format);
            }
            // Init connect
            $this->initConnect();
            // Get content
            $result = $this->execConnect();
            // Close connect
            $this->closeConnect();
            // Verify this is multiple text
            if (!is_array($text)) {
                // Get only info necessary
                $result = current($result->translations);

                if (isset($result->detectedSourceLanguage)) {
                    // Return by reference the language in case detected language
                    $sourceLanguage = $result->detectedSourceLanguage;
                }

                // Return translate
                return $result->translatedText;
            } else {
                // This is multiple text
                // Get only info necessary
                $result = $result->translations;
                // Save translate list
                $arrTranslateReturn = array();
                // Save source list
                $arrSourceReturn = array();
                // Get translates
                foreach ($result as $itemResult) {
                    $arrTranslateReturn[] = $itemResult->translatedText;
                    if (isset($itemResult->detectedSourceLanguage)) {
                        $arrSourceReturn[] = $itemResult->detectedSourceLanguage;
                    }
                }
                // Return by reference the language in case detected language
                $sourceLanguage = $arrSourceReturn;
                // Return list of translate
                return $arrTranslateReturn;
            }
        } else {
            return false;
        }
    }

    /**
     * Deletect Language
     * @param string|array $text The text or list the text to be detect
     * @return string|array language or list of language detected
     */
    public function detect($text) {
        if ($this->isValid($text, null, null, false)) {
            reset($text);

            // Add keyAccess
            $this->addQueryParam('key', $this->accessKey);
            // Add text to be translate
            $this->addQueryParam('q', $text);
            // Init connect
            $this->initConnect(self::SERVICE_DETECT);
            // Get content
            $result = $this->execConnect();
            // Close connect
            $this->closeConnect();
            // Verify this is multiple text
            if (count($result->detections) == 1) {
                // Get only info necessary
                $result = current(current($result->detections));
                // Return translate
                return $result->language;
            } else {
                // This is multiple text
                // Get only info necessary
                $result = $result->detections;
                // Save detect language list
                $arrSourceReturn = array();
                // Get translates
                foreach ($result as $itemResult) {
                    $itemResult = current($itemResult);
                    $arrSourceReturn[] = $itemResult->language;
                }
                // Return list of detect language
                return $arrSourceReturn;
            }
        } else {
            return false;
        }
    }

    /**
     * Request Language support list
     * @param type $target
     * @return array|boolean
     */
    public function languageSupport($target = null) {
        // Add keyAccess
        $this->addQueryParam('key', $this->accessKey);
        if (!is_null($target)) {
            if ($this->validLanguage($target)) {
                $this->addQueryParam('target', $target);
            } else {
                return false;
            }
        }
        // Init connect
        $this->initConnect(self::SERVICE_LANGUAGE);
        // Get content
        $result = $this->execConnect();
        // Close connect
        $this->closeConnect();

        // Get only info necessary
        $result = $result->languages;
        // Return list of language support
        return $result;
    }

    /**
     * Validate info
     * @param string|array $text The text or list the text to be validate
     * @param string $targetLanguage target language to be validate
     * @param string $sourceLanguage source language to be validate
     * @param boolean $targetRequired the target language is required?
     * @return boolean
     */
    private function isValid(& $text, $targetLanguage = null, $sourceLanguage = null, $targetRequired = true) {
        // Check text/texts
        // In case of numeric only in text, return false
        if (!is_array($text)) {
            if (!is_string($text) || is_numeric($text) || strlen($text) < 2) {
                return false;
            }
        } else {
            foreach ($text as $keyText => $oneText) {
                // Is numeric?
                if (!is_string($oneText) || is_numeric($oneText) || strlen($oneText) < 2) {
                    // Remove from list translate
                    unset($text[$keyText]);
                }
            }
        }
        // No text valid?
        if (count($text) <= 0) {
            return false;
        }
        // Target required?
        if ($targetRequired) {
            // Is valid language target?
            if (!$this->validLanguage($targetLanguage)) {
                return false;
            }
        }
        // Is valid language source?
        if (!is_null($sourceLanguage)) {
            if (!$this->validLanguage($sourceLanguage)) {
                return false;
            }
        }
        // Valid!
        return true;
    }

    /**
     * Validate language with a regular expression:<br/>
     * 2 characters + hypens + 2 characters ( <b>%([a-z]{2})(-[A-Z]{2})?%</b> ).<br/>
     * Real supported languages list available at https://cloud.google.com/translate/v2/using_rest#language-params<br/>
     * @param string $lang language text to be validate
     * @return boolean
     */
    public function validLanguage($lang) {
        $regexpValidLanguage = '%([a-z]{2})(-[A-Z]{2})?%';
        if (preg_match($regexpValidLanguage, $lang) == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Init the connect with parameters set in <b>$this->parameters</b>.<br/>
     * Connection set in <b>$this->connect</b>.
     * @param string $service           One of the values of GoogleTranslate's SERVICE_DETECT, SERVICE_LANGUAGE or SERVICE_TRANSLATE contants. Defaults to SERVICE_TRANSLATE.
     * @throws GoogleTranslateException
     */
    private function initConnect($service = self::SERVICE_TRANSLATE) {
        // Without API key nothing can be done!
        if (!isset($this->parameters['key'])) {
            throw new GoogleTranslateException('Missing API key ("key").', 2);
        }
        // Choose service
        switch ($service) {
            case self::SERVICE_DETECT:
                $url = $this->apiUri . '/detect';
                if (!isset($this->parameters['q'])) {
                    throw new GoogleTranslateException('Missing one or more required parameters ("q").', 3);
                }
                break;
            case self::SERVICE_LANGUAGE:
                $url = $this->apiUri . '/languages';
                break;
            case self::SERVICE_TRANSLATE:
            default:
                $url = $this->apiUri;
                // Check mandatory parameters
                if (!isset($this->parameters['q']) || !isset($this->parameters['target'])) {
                    throw new GoogleTranslateException('Missing one or more required parameters ("q", "target").', 3);
                }
        }

        // Text must be smaller than 5K chars
        if (isset($this->parameters['q'])) {
            if (is_array($this->parameters['q'])) {
                foreach ($this->parameters['q'] as $text) {
                    $text_len = strlen($text);
                    if ($text_len > 5000) {
                        throw new GoogleTranslateException("Can't translate more than 5K chars for every texts.", 5);
                    }
                }
            } else {
                $text_len = strlen($this->parameters['q']);
                if ($text_len > 5000) {
                    throw new GoogleTranslateException("Can't translate more than 5K chars.", 5);
                }
            }
        }

        if (!isset($this->parameters['prettyprint'])) {
            $this->addQueryParam('prettyprint', false);
        }

        // If GET url is longer than 2K char, request must be POST
        if (!empty($this->parameters)) {
            $query = $this->http_build_query();
            if (strlen($url . '?' . $query) < 2000) {
                // Init curl as GET request with http query parameters
                $url .= '?' . $query;
                $this->connect = curl_init($url);
            } else {
                // Init curl as POST request
                $this->connect = curl_init($url);
                /**
                 * If $this->parameters is multidimensional count($this->prameters) won't be correct
                 * so we count the exploded query string
                 */
                curl_setopt($this->connect, CURLOPT_POST, count(explode('&', $query)));
                curl_setopt($this->connect, CURLOPT_POSTFIELDS, $query);
                curl_setopt($this->connect, CURLOPT_HTTPHEADER, array(
                    'X-HTTP-Method-Override: GET'
                ));
            }
        } else {
            // Init curl as empty GET request
            $this->connect = curl_init($url);
        }

        // Set to return data received
        curl_setopt($this->connect, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Build query url
     * @return type
     */
    private function http_build_query() {
        //add for each item
        $query_parts = array();
        foreach ($this->parameters as $keyParam => $param) {
            switch ($keyParam) {
                case 'q':
                    if (is_array($param)) {
                        foreach ($param as $subParam) {
                            $query_parts[] = 'q=' . urlencode($subParam);
                        }
                    } else {
                        $query_parts[] = 'q=' . urlencode($param);
                    }
                    break;
                default:
                    $query_parts[] = urlencode($keyParam) . '=' . urlencode($param);
            }
        }
        return implode('&', $query_parts);
    }

    /**
     * Add Query parameters for the API request (on $this->paramters)
     * @param type $key
     * @param type $value
     */
    private function addQueryParam($key, $value) {
        // Remove possible whitespaces, utf8 encode AND add to params list
        if (is_array($value)) {
            foreach ($value as $valKey => $itemValue) {
                $value[$valKey] = utf8_encode($itemValue);
            }
        } else {
            $value = utf8_encode($value);
        }
        // Add to param list
        $cleanKey = utf8_encode(str_replace(' ', '', $key));
        $this->parameters[$cleanKey] = $value;
    }

    /**
     * Close the CURL request ($this->connect) and reset parameters.
     */
    private function closeConnect() {
        // Close curl connect
        curl_close($this->connect);
        // Clear params to next request
        $this->parameters = array();
    }

    /**
     * Execute CURL $this->connect and return array with data (already JSON decoded)
     * @return array
     * @throws GoogleTranslateException
     */
    private function execConnect() {
        // Exec curl
        $result = curl_exec($this->connect);
        // Transform json in stdClass
        $result = json_decode($result);

        // Get request info
        $arrInfo = curl_getinfo($this->connect);
        // Found?
        if ($arrInfo['http_code'] == 404) {
            // No connect
            throw new GoogleTranslateException('Not Found Request', 404);
        }

        if (($arrInfo['http_code'] == 200 || $arrInfo['http_code'] == 304) && !isset($result->error)) {
            // Request ok, return data
            return $result->data;
        } else {
            // Invalid key?
            throw new GoogleTranslateException($result->error->message, $result->error->code, null, array('info' => $arrInfo, 'parameters' => $this->parameters, 'response' => $result));
        }
    }

}

class GoogleTranslateException extends Exception {

    public $message;
    public $code;
    public $stack;

    function __construct($message, $code = null, $previus = null, $var_to_dump = null) {
        $this->stack = $var_to_dump;
        parent::__construct($message, $code, $previus);
    }

}
