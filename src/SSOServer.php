<?php
namespace Dolf\SSOServer;


class SSOServer
{
    /**
     * Url of SSO server
     * @var string
     */
    protected $serverUrl;

    /**
     * Url of broker
     * @var array
     */
    public $brokerUrl;

    /**
     * User data
     * @var array
     */
    protected $userData;

    /**
     * API key
     * @var mixed
     */
    protected $apiKey;


    /**
     * SSOServer constructor.
     * @param $serverUrl
     * @param $brokerUrl
     * @param $userData
     * @param $apiKey
     */
    public function __construct($serverUrl, $brokerUrl, $userData, $apiKey)
    {
        $this->serverUrl = $serverUrl;
        $this->brokerUrl = $brokerUrl;
        $this->userData  = $userData;
        $this->apiKey    = $apiKey;
    }

    /**
     * Redirect and login to broker site, redirect back
     */
    public function ssoAction($action)
    {
        $destination = isset($_REQUEST['destination']) ?: $this->serverUrl;

        if (strpos($destination, "sso") !== false) {
            $destination = env('BROKER_URL');
        }

        $encryptedData = $this->encrypt();

        $url = $this->getSSOUrl($encryptedData, $destination,  $action);

        header("Location: $url", true, 307);
        exit();
    }

    /**
     * Encrypt user data
     *
     * @return array
     */
    public function encrypt()
    {
        // Create the encryption key using a 16 byte SHA1 digest of your api key and brokerUrl

        $salted = $this->apiKey . $this->brokerUrl;
        $digest = hash('sha1', $salted, true);
        $key    = substr($digest, 0, 16);

        // Generate a random 16 byte IV

        $iv = mcrypt_create_iv(16);

        // Build json data

        $data = json_encode($this->userData);

        // PHP's mcrypt library does not perform padding by default
        // Pad using standard PKCS#5 padding with block size of 16 bytes

        $pad = 16 - (strlen($data) % 16);
        $data = $data . str_repeat(chr($pad), $pad);

        // Encrypt data using AES128-cbc

        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'','cbc','');
        mcrypt_generic_init($cipher, $key, $iv);
        $multipass = mcrypt_generic($cipher,$data);
        mcrypt_generic_deinit($cipher);

        // Prepend the IV to the encrypted data
        // This will be extracted and used for decryption

        $multipass = $iv . $multipass;

        // Base64 encode the encrypted data

        $multipass = base64_encode($multipass);

        // Build an HMAC-SHA1 signature using the encoded string and your api key

        $signature = hash_hmac("sha1", $multipass, $this->apiKey, true);

        // Base64 encode the signature

        $signature = base64_encode($signature);

        // Finally, URL encode the multipass and signature

        $multipass = urlencode($multipass);
        $signature = urlencode($signature);


        return $data = [
            'multipass' => $multipass,
            'signature' => $signature,
        ];
    }


    /**
     * Create GET request with encrypted user data and signature
     *
     * @param $encryptedData
     * @param $destination
     * @param $action
     * @return string
     * @internal param $data
     */
    public function getSSOUrl($encryptedData, $destination, $action)
    {
        $returnUrl = ['returnUrl' => $destination];

        $data = [
            'action'     => $action,
            'multipass'  => $encryptedData['multipass'],
            'signature'  => $encryptedData['signature'],
        ] + $_GET;

        return $this->brokerUrl . '/sso' . "?" . http_build_query($data + $returnUrl);
    }
}


