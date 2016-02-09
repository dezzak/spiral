<?php
/**
 * Spiral: PSR7 aware cURL client (https://github.com/juliangut/spiral)
 *
 * @link https://github.com/juliangut/spiral for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/spiral/master/LICENSE
 */

namespace Jgut\Spiral\Option;

use Jgut\Spiral\Exception\OptionException;
use Jgut\Spiral\Option;
use Jgut\Spiral\Option\OptionCallback;

/**
 * cURL option wrappers factory.
 */
abstract class OptionFactory
{
    /**
     * Accepted cURL option aliases.
     *
     * @var array
     */
    private static $aliasMap = [
        'http-header'        => CURLOPT_HTTPHEADER,

        'crlf'               => CURLOPT_CRLF,
        'header-out'         => CURLINFO_HEADER_OUT,
        'return-transfer'    => CURLOPT_RETURNTRANSFER,
        'verbose'            => CURLOPT_VERBOSE,
        'user-agent'         => CURLOPT_USERAGENT,
        'ssl-version'        => CURLOPT_SSLVERSION,
        'cookie-file'        => CURLOPT_COOKIEFILE,
        'cookie-jar'         => CURLOPT_COOKIEJAR,
        'referer'            => CURLOPT_REFERER,
        'auto-referer'       => CURLOPT_AUTOREFERER,
        'file-time'          => CURLOPT_FILETIME,
        'user-password'      => CURLOPT_USERPWD,
        'http-version'       => CURLOPT_HTTP_VERSION,
        'port'               => CURLOPT_PORT,
        'encoding'           => CURLOPT_ENCODING,
        'buffer-size'        => CURLOPT_BUFFERSIZE,
        'post-redir'         => CURLOPT_POSTREDIR,
        'stderr'             => CURLOPT_STDERR,
        'netrc'              => CURLOPT_NETRC,

        'header'             => CURLOPT_HEADER,
        'include'            => CURLOPT_HEADER,

        'connect-timeout'    => CURLOPT_CONNECTTIMEOUT,
        'connection-timeout' => CURLOPT_CONNECTTIMEOUT,

        'timeout'            => CURLOPT_TIMEOUT,
        'max-time'           => CURLOPT_TIMEOUT,

        'ssl_verify_host'    => CURLOPT_SSL_VERIFYHOST,
        'ssl_verify_peer'    => CURLOPT_SSL_VERIFYPEER,
        'insecure'           => CURLOPT_SSL_VERIFYPEER,

        'follow-location'    => CURLOPT_FOLLOWLOCATION,
        'follow-redirects'   => CURLOPT_FOLLOWLOCATION,
        'location'           => CURLOPT_FOLLOWLOCATION,

        'max-redirs'         => CURLOPT_MAXREDIRS,
        'max-redirects'      => CURLOPT_MAXREDIRS,

        'cookie'             => CURLOPT_COOKIE,
        'cookies'            => CURLOPT_COOKIE,

        'http-auth'          => CURLOPT_HTTPAUTH,
        'auth'               => CURLOPT_HTTPAUTH,

        'unrestricted-auth'  => CURLOPT_UNRESTRICTED_AUTH,
        'location-trusted'   => CURLOPT_UNRESTRICTED_AUTH,

        'forbid-reuse'       => CURLOPT_FORBID_REUSE,
        'fresh-connect'      => CURLOPT_FRESH_CONNECT,
    ];

    /**
     * cURL accepted options and class mapper.
     *
     * @var array
     */
    private static $typeMap = [
        // Boolean
        // Convert Unix newlines to CRLF newlines on transfers
        CURLOPT_CRLF              => ['type' => 'bool'],
        // Include request header in the output
        CURLINFO_HEADER_OUT       => ['type' => 'bool'],
        // Include response header in the output
        CURLOPT_HEADER            => ['type' => 'bool'],
        // Return the transfer as a string instead of outputting
        CURLOPT_RETURNTRANSFER    => ['type' => 'bool'],
        // Output verbose information
        CURLOPT_VERBOSE           => ['type' => 'bool'],
        // False to stop cURL from verifying the peer's certificate
        CURLOPT_SSL_VERIFYPEER    => ['type' => 'bool'],
        // Follow any Location headers sent by server
        CURLOPT_FOLLOWLOCATION    => ['type' => 'bool'],
        // Automatically set the http referer header when following a redirect
        CURLOPT_AUTOREFERER       => ['type' => 'bool'],
        // Keep sending the username and password when following locations
        CURLOPT_UNRESTRICTED_AUTH => ['type' => 'bool'],
        // Get HTTP header for modification date of file
        CURLOPT_FILETIME          => ['type' => 'bool'],
        // Automatically close connection after processing
        CURLOPT_FORBID_REUSE      => ['type' => 'bool'],
        // Force to use new connection instead of cached
        CURLOPT_FRESH_CONNECT     => ['type' => 'bool'],
        // Scan ~/.netrc file for user credentials
        CURLOPT_NETRC             => ['type' => 'bool'],

        // Integer
        // Number of seconds to wait while trying to connect. 0 to wait indefinitely
        CURLOPT_CONNECTTIMEOUT => ['type' => 'int'],
        // Maximum number of seconds to allow cURL functions to execute
        CURLOPT_TIMEOUT        => ['type' => 'int'],
        // The maximum amount of HTTP redirections to follow
        CURLOPT_MAXREDIRS      => ['type' => 'int'],
        // Alternative port number to connect to
        CURLOPT_PORT           => ['type' => 'int', 'max' => 99999],
        // Size of the buffer for each read
        CURLOPT_BUFFERSIZE     => ['type' => 'int'],

        // String
        // Contents of the "User-Agent: " header to be used in a HTTP request
        CURLOPT_USERAGENT => ['type' => 'string'],
        // Contents of the "Referer: " header to be used in a HTTP request
        CURLOPT_REFERER   => ['type' => 'string'],
        // Contents of the "Accept-Encoding: " header. This enables decoding of the response
        CURLOPT_ENCODING  => ['type' => 'string'],
        // Alternative location to output errors
        CURLOPT_STDERR    => ['type' => 'string'],

        // File
        // Name of the file containing the cookie data
        CURLOPT_COOKIEFILE => ['type' => 'file'],
        // Name of a file to save all internal cookies to when the handle is closed
        CURLOPT_COOKIEJAR  => ['type' => 'file'],

        // Regex
        // Which SSL version (2 or 3) to use
        CURLOPT_SSLVERSION        => [
            'type' => 'regex',
            'regex' => '/^[0-6]$/',
            'message' => '"%s" is not valid SSL version'
        ],
        // Verify existence of a common name in peer certificate, and matches hostname
        CURLOPT_SSL_VERIFYHOST => [
            'type' => 'regex',
            'regex' => '/^[12]$/',
            'message' => '"%s" is not valid SSL verify host value'
        ],
        // Bit mask to maintain redirection type
        CURLOPT_POSTREDIR => [
            'type' => 'regex',
            'regex' => '/^[124]$/',
            'message' => '"%s" is not valid POST redirection value'
        ],
        // Username and password formatted as "[username]:[password]" to use for the connection
        CURLOPT_USERPWD => [
            'type' => 'regex',
            'regex' => '/^[^\n:]+:[^\n:]+$/',
            'message' => '"%s" is not a valid CURLOPT_USERPWD value'
        ],

        // Callback
        /*
        HTTP authentication method(s) to use:
            CURLAUTH_BASIC, CURLAUTH_DIGEST, CURLAUTH_GSSNEGOTIATE, CURLAUTH_NTLM, CURLAUTH_ANY, CURLAUTH_ANYSAFE
        Currently only CURLAUTH_BASIC is available and implemented as boolean
        */
        CURLOPT_HTTPAUTH     => ['type' => 'callback'],
        // Which HTTP version to use. "1.0" for CURL_HTTP_VERSION_1_0 or "1.1" for CURL_HTTP_VERSION_1_1
        CURLOPT_HTTP_VERSION => ['type' => 'callback'],
        // Contents of the "Cookie: " header to be used in the HTTP request. Can be an array
        CURLOPT_COOKIE       => ['type' => 'callback'],
    ];

    /**
     * Build cURL option.
     *
     * @param int|string $option
     * @param mixed      $value
     *
     * @return \Jgut\Spiral\Option
     *
     * @throws \Jgut\Spiral\Exception\OptionException
     */
    public static function build($option, $value)
    {
        $optionDefinition = ['type' => ''];

        $option = static::getOptionKey($option);
        if (array_key_exists($option, static::$typeMap)) {
            $optionDefinition = static::$typeMap[$option];
        }

        $optionClassName = sprintf('\Jgut\Spiral\Option\\Option%s', ucfirst($optionDefinition['type']));
        $optionClass = new $optionClassName($option);

        switch (strtolower($optionDefinition['type'])) {
            case 'regex':
                /** @var \Jgut\Spiral\Option\OptionRegex $optionClass */
                $optionClass->setRegex($optionDefinition['regex']);
                if (array_key_exists('message', $optionDefinition)) {
                    $optionClass->setMessage($optionDefinition['message']);
                }
                break;

            case 'int':
                /** @var \Jgut\Spiral\Option\OptionInt $optionClass */
                $optionClass->setMin(array_key_exists('min', $optionDefinition) ? $optionDefinition['min'] : 0);
                if (array_key_exists('max', $optionDefinition)) {
                    $optionClass->setMax($optionDefinition['max']);
                }
                break;

            case 'callback':
                $optionClass = static::configureCallback($optionClass, $option);
                break;
        }

        $optionClass->setValue($value);

        return $optionClass;
    }

    /**
     * Get mapped option.
     *
     * @param int|string $option
     *
     * @return int
     *
     * @throws \Jgut\Spiral\Exception\OptionException
     */
    public static function getOptionKey($option)
    {
        if (is_string($option)) {
            $option = strtolower(preg_replace('/[ _]+/', '-', trim($option)));
            if (array_key_exists($option, static::$aliasMap)) {
                $option = static::$aliasMap[strtolower($option)];
            }
        }

        $curlConstants = array_filter(
            get_defined_constants(true)['curl'],
            function ($val, $key) {
                return is_int($val) && strpos($key, 'CURLOPT_') === 0;
            },
            ARRAY_FILTER_USE_BOTH
        );

        if (!in_array($option, $curlConstants, true)) {
            throw new OptionException(sprintf('"%s" is not valid cURL option', $option));
        }

        return $option;
    }

    /**
     * Configure option callback.
     *
     * @param \Jgut\Spiral\Option\OptionCallback $optionClass
     * @param                                    $option
     *
     * @return \Jgut\Spiral\Option\OptionCallback
     */
    protected static function configureCallback(OptionCallback $optionClass, $option)
    {
        switch ($option) {
            case CURLOPT_HTTPAUTH:
                $optionClass->setCallback(function ($value) {
                    return $value === false ? false : CURLAUTH_BASIC;
                });
                break;

            case CURLOPT_HTTP_VERSION:
                $optionClass->setCallback(function ($value) {
                    $value = number_format((float) $value, 1, '.', '');

                    if (!preg_match('/^1.(0|1)$/', $value)) {
                        throw new OptionException(sprintf('"%s" is not a valid HTTP version', $value));
                    }

                    return constant('CURL_HTTP_VERSION_' . str_replace('.', '_', $value));
                });
                break;

            case CURLOPT_COOKIE:
                $optionClass->setCallback(function ($value) {
                    if (is_array($value)) {
                        $value = http_build_query($value, '', '; ');
                    }

                    return $value;
                });
                break;
        }

        return $optionClass;
    }
}
