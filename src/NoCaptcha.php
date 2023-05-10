<?php

namespace ZBrettonYe\NoCaptcha;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

class NoCaptcha
{
    public const CLIENT_API = 'https://www.recaptcha.net/recaptcha/api.js';
    public const VERIFY_URL = 'https://www.recaptcha.net/recaptcha/api/siteverify';

    /**
     * The recaptcha secret key.
     *
     * @var string
     */
    private $secret;

    /**
     * The recaptcha sitekey key.
     *
     * @var string
     */
    private $sitekey;

    /**
     * @var Client
     */
    private $http;

    /**
     * The cached verified responses.
     *
     * @var array
     */
    private $verifiedResponses = [];

    public function __construct($secret, $sitekey, $options = [])
    {
        $this->secret = $secret;
        $this->sitekey = $sitekey;
        $this->http = new Client($options);
    }

    public function displayWidget(array $attributes = []): string
    {
        return $this->display($attributes);
    }

    // Render HTML captcha.
    public function display($attributes = []): string
    {
        $attributes = $this->prepareAttributes($attributes);

        return '<div'.$this->buildAttributes($attributes).'></div>';
    }

    // Prepare HTML attributes and assure that the correct classes and attributes for captcha are inserted.
    protected function prepareAttributes(array $attributes): array
    {
        $attributes['data-sitekey'] = $this->sitekey;
        if (! isset($attributes['class'])) {
            $attributes['class'] = '';
        }
        $attributes['class'] = trim('g-recaptcha '.$attributes['class']);

        return $attributes;
    }

    // Build HTML attributes.
    protected function buildAttributes(array $attributes): string
    {
        $html = [];

        foreach ($attributes as $key => $value) {
            $html[] = $key.'="'.$value.'"';
        }

        return count($html) ? ' '.implode(' ', $html) : '';
    }

    /**
     * Display a Invisible reCAPTCHA by embedding a callback into a form submit button.
     *
     * @param  string  $formIdentifier  the html ID of the form that should be submitted.
     * @param  string  $text  the text inside the form button
     * @param  array  $attributes  array of additional html elements
     *
     * @return string
     */
    public function displaySubmit(string $formIdentifier, string $text = 'submit', array $attributes = []): string
    {
        $javascript = '';
        if (! isset($attributes['data-callback'])) {
            $functionName = 'onSubmit'.str_replace(['-', '=', '\'', '"', '<', '>', '`'], '', $formIdentifier);
            $attributes['data-callback'] = $functionName;
            $javascript = sprintf(
                '<script>function %s(){document.getElementById("%s").submit();}</script>',
                $functionName,
                $formIdentifier
            );
        }

        $attributes = $this->prepareAttributes($attributes);

        $button = sprintf('<button%s><span>%s</span></button>', $this->buildAttributes($attributes), $text);

        return $button.$javascript;
    }

    // Render js source
    public function renderJs(string $lang = null, bool $callback = false, string $onLoadClass = 'onloadCallBack'): string
    {
        return '<script src="'.$this->getJsLink($lang, $callback, $onLoadClass).'" async defer></script>'."\n";
    }

    // Get recaptcha js link.
    public function getJsLink(string $lang = null, bool $callback = false, string $onLoadClass = 'onloadCallBack'): string
    {
        $client_api = static::CLIENT_API;
        $params = [];

        $callback ? $this->setCallBackParams($params, $onLoadClass) : false;
        $lang ? $params['hl'] = $lang : null;

        return $client_api.'?'.http_build_query($params);
    }

    protected function setCallBackParams(&$params, $onLoadClass): void
    {
        $params['render'] = 'explicit';
        $params['onload'] = $onLoadClass;
    }

    // Verify no-captcha response by Symfony Request.
    public function verifyRequest(Request $request): bool
    {
        return $this->verifyResponse(
            $request->get('g-recaptcha-response'),
            $request->getClientIp()
        );
    }

    // Verify no-captcha response.
    public function verifyResponse(string $response, string $clientIp = null): bool
    {
        if (empty($response)) {
            return false;
        }

        // Return true if response already verfied before.
        if (in_array($response, $this->verifiedResponses, true)) {
            return true;
        }

        $verifyResponse = $this->sendRequestVerify([
            'secret'   => $this->secret,
            'response' => $response,
            'remoteip' => $clientIp,
        ]);

        if (isset($verifyResponse['success']) && $verifyResponse['success'] === true) {
            // A response can only be verified once from google, so we need to
            // cache it to make it work in case we want to verify it multiple times.
            $this->verifiedResponses[] = $response;

            return true;
        }

        return false;
    }

    // Send verify request.
    protected function sendRequestVerify(array $query = []): array
    {
        $response = $this->http->request('POST', static::VERIFY_URL, [
            'form_params' => $query,
        ]);

        return json_decode($response->getBody(), true);
    }
}
