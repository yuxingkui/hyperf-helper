<?php

declare(strict_types=1);

namespace Yuxk\Helpers;

/**
 * JwtAuth
 * Class JwtAuthAuth.
 */
class JwtAuth
{
    /**
     * token.
     */
    public static string $token;

    /**
     * salt.
     */
    public static string $salt;

    public function __construct(string $token = '')
    {
        // 这里是自定义的一个随机字串，应该写在config文件中的，解密时也会用，相当    于加密中常用的 盐  salt
        self::$salt = JwtAuth::urlsafeB64Decode(env('JWT_SECRET', 'default'));
        self::$token = $token;
    }


    /**
     * @param bool $verify Don't skip verification process
     *
     * @return object The JwtAuth's payload as a PHP object
     * @throws \Exception
     */
    public static function decode(bool $verify = true): object
    {
        $tks = explode('.', self::$token);
        if (count($tks) != 3) {
            throw new \Exception('Wrong number of segments');
        }
        list($headb64, $payloadb64, $cryptob64) = $tks;
        if (null === ($header = JwtAuth::jsonDecode(JwtAuth::urlsafeB64Decode($headb64)))
        ) {
            throw new \Exception('Invalid segment encoding');
        }
        if (null === $payload = JwtAuth::jsonDecode(JwtAuth::urlsafeB64Decode($payloadb64))
        ) {
            throw new \Exception('Invalid segment encoding');
        }
        $sig = JwtAuth::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                throw new \Exception('Empty algorithm');
            }
            if ($sig != JwtAuth::sign("$headb64.$payloadb64", JwtAuth::$salt, $header->alg)) {
                throw new \Exception('Signature verification failed');
            }
        }

        return $payload;
    }

    /**
     * @param object|array $payload PHP object or array
     * @param string $algo The signing algorithm
     *
     * @return string A JwtAuth
     * @throws \Exception
     */
    public static function encode($payload, string $algo = 'HS256'): string
    {
        $header = array('typ' => 'JwtAuth', 'alg' => $algo);

        $segments = array();
        $segments[] = JwtAuth::urlsafeB64Encode(JwtAuth::jsonEncode($header));
        $segments[] = JwtAuth::urlsafeB64Encode(JwtAuth::jsonEncode($payload));
        $signing_input = implode('.', $segments);

        $signature = JwtAuth::sign($signing_input, self::$salt, $algo);
        $segments[] = JwtAuth::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    /**
     * @param string $msg The message to sign
     * @param string $key The secret key
     * @param string $method The signing algorithm
     *
     * @return string An encrypted message
     * @throws \Exception
     */
    public static function sign(string $msg, string $key, string $method = 'HS256'): string
    {
        $methods = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        if (empty($methods[$method])) {
            throw new \Exception('Algorithm not supported');
        }

        return hash_hmac($methods[$method], $msg, $key, true);
    }

    /**
     * @param string $input JSON string
     *
     * @return object Object representation of JSON string
     * @throws \Exception
     */
    public static function jsonDecode(string $input): object
    {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            JwtAuth::handleJsonError($errno);
        } else {
            if ($obj === null && $input !== 'null') {
                throw new \Exception('Null result with non-null input');
            }
        }

        return $obj;
    }

    /**
     * @param object|array $input A PHP object or array
     *
     * @return string JSON representation of the PHP object or array
     * @throws \Exception
     */
    public static function jsonEncode($input): string
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            JwtAuth::handleJsonError($errno);
        } else {
            if ($json === 'null' && $input !== null) {
                throw new \Exception('Null result with non-null input');
            }
        }

        return $json;
    }

    /**
     * @param string $input A base64 encoded string
     *
     * @return string A decoded string
     */
    public static function urlsafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }

        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @param string $input Anything really
     *
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * @param int $errno An error number from json_last_error()
     *
     * @return void
     * @throws \Exception
     */
    private static function handleJsonError(int $errno): void
    {
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        );
        throw new \Exception(
            $messages[$errno] ?? 'Unknown JSON error: '.$errno
        );
    }
}


