<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Zend_Service_Google
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_Google_C2dm_Message
 */
require_once 'Zend/Service/Google/C2dm/Message.php';

/**
 * Service Google C2dm
 * Implementation for Android Cloud to Device Messaging
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Zend_Service_Google
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Google_C2dm
{
    /**
     * @var string
     */
    const C2DM_SEND_URI = 'https://android.apis.google.com/c2dm/send';

    /**
     * @var string
     */
    const AUTH_SERVICE_NAME = 'ac2dm';

    /**
     * @var string
     */
    protected $_defaultPostUri = self::C2DM_SEND_URI;

    /**
     * @var Zend_Http_Client
     */
    protected $_client;

    /**
     * @var string
     */
    protected $_loginToken;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var Zend_Service_Google_C2dm_Message
     */
    protected $_lastMessage;

    /**
     * @var Zend_Http_Response
     */
    protected $_lastResponse;

    /**
     * Constructor
     *
     * @param array|Zend_Config $options
     * @return Zend_Service_Google_C2dm
     */
    public function __construct($options=array())
    {
        $this->setOptions($options);
    }

    /**
     * Get Options for C2DM
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set Options for C2DM
     *
     * @param array|Zend_Config $options
     * @return Zend_Service_Google_C2dm
     * @throws Zend_Service_Google_C2dm_Exception
     */
    public function setOptions($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            require_once 'Zend/Service/Google/C2dm/Exception.php';
            throw new Zend_Service_Google_C2dm_Exception('setOptions() expects either an array or a Zend_Config object');
        }

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->_options[$k] = $v;
            }
        }

        return $this;
    }

    /**
     * Get Login Token
     *
     * @return string
     */
    public function getLoginToken()
    {
        return $this->_loginToken;
    }

    /**
     * Set Login Token
     *
     * @param string $token
     * @return Zend_Service_Google_C2dm
     * @throws Zend_Service_Google_C2dm_Exception
     */
    public function setLoginToken($token) {
        if (!is_string($token)) {
            require_once 'Zend/Service/Google/C2dm/Exception.php';
            throw new Zend_Service_Google_C2dm_Exception('setLoginToken() expects a string');
        }
        $this->_loginToken = $token;
        return $this;
    }

    /**
     * Get Http Client
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (!$this->_client instanceof Zend_Http_Client) {
            $this->_client = new Zend_Http_Client();
            $this->_client->setConfig(array(
                'strictredirects' => true,
            ));
        }
        return $this->_client;
    }

    /**
     * Set Http Client
     *
     * @return Zend_Service_Google_C2dm
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_client = $client;
        return $this;
    }

    /**
     * Prepare an HTTP Request for C2DM
     *
     * @return void
     * @throws Zend_Service_Google_C2dm_Exception
     */
    protected function _prepareHttpRequest()
    {
        $client = $this->getHttpClient();
        $token = $this->getLoginToken();
        if (empty($token)) {
            throw new Zend_Service_Google_C2dm_Exception('Sending a message requires a Google Authorization Token');
        }
        $client->setUri($this->_defaultPostUri);
        $client->setHeaders('Authorization', 'GoogleLogin auth=' . $this->getLoginToken());
        if (array_key_exists('delay_while_idle', $this->_options) && $this->_options['delay_while_idle']) {
            $client->setParameterPost('delay_while_idle', 1);
        }
        $this->_client = $client;
    }

    /**
     * Send a Message
     *
     * @param Zend_Service_Google_C2dm_Message $message
     * @return boolean
     * @throws Zend_Service_Google_C2dm_Exception
     */
    public function sendMessage(Zend_Service_Google_C2dm_Message $message)
    {
        $this->_lastMessage = $message;
        if (!$message->validate()) {
            require_once 'Zend/Service/Google/C2dm/Exception.php';
            throw new Zend_Service_Google_C2dm_Exception('sendMessage was unable to validate the message');
        }
        $this->_prepareHttpRequest();
        $client = $this->getHttpClient();
        $client->setParameterPost('registration_id', $message->getRegistrationId());
        $client->setParameterPost('collapse_key', $message->getCollapseKey());
        foreach ($message->getData() as $k => $v) {
            $client->setParameterPost('data.' . $k, $v);
        }

        $this->_lastResponse = $response= $client->request('POST');

        // check the response for errors:
        switch ($response->getStatus()) {
            case 503:
                require_once 'Zend/Service/Google/C2dm/Exception/ServerUnavailable.php';
                throw new Zend_Service_Google_C2dm_Exception_ServerUnavailable('The server was unavailable, check Retry-After and try again');
                break;
            case 401:
                require_once 'Zend/Service/Google/C2dm/Exception/InvalidAuthToken.php';
                throw new Zend_Service_Google_C2dm_Exception_InvalidAuthToken('The Auth token: ' . $this->getLoginToken() . ' was invalid');
                break;
            default:
                // check response body for any errors.
                // parse out the message:
                $body = $response->getBody();
                $body = preg_split('/=/', $body);
                if (!isset($body[0]) || !isset($body[1])) {
                    // bad response from google
                    require_once 'Zend/Service/Google/C2dm/Exception/ServerUnavailable.php';
                    throw new Zend_Service_Google_C2dm_Exception_ServerUnavailable('The server gave us an invalid response, we need to try again.');
                }
                if (strtolower($body[0]) == 'error') {
                    $exception = "Zend_Service_Google_C2dm_Exception_{$body[1]}";
                    require_once str_replace('_', '/', $exception) . '.php';
                    throw new $exception();
                }
        }
        return true;
    }

    /**
     * Gets the Last Sent Message
     *
     * @return Zend_Service_Google_C2dm_Message
     */
    public function getLastMessage()
    {
        return $this->_lastMessage;
    }

    /**
     * Gets the Last Response
     *
     * @return Zend_Http_Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }
}
