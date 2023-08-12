<?php

use PHPUnit\Framework\TestCase;
use ZBrettonYe\NoCaptcha\NoCaptcha;

class NoCaptchaTest extends TestCase
{
    /**
     * @var NoCaptcha
     */
    private $captcha;

    public function setUp(): void
    {
        parent::setUp();
        $this->captcha = new NoCaptcha('{secret-key}', '{site-key}');
    }

    public function testRequestShouldWorks()
    {
        $response = $this->captcha->verifyResponse('should_false');
    }

    public function testJsLink()
    {
        self::assertInstanceOf(NoCaptcha::class, $this->captcha);

        $simple = '<script src="https://www.recaptcha.net/recaptcha/api.js?" async defer></script>'."\n";
        $withLang = '<script src="https://www.recaptcha.net/recaptcha/api.js?hl=vi" async defer></script>'."\n";
        $withCallback = '<script src="https://www.recaptcha.net/recaptcha/api.js?render=explicit&onload=myOnloadCallback" async defer></script>'."\n";

        self::assertEquals($simple, $this->captcha->renderJs());
        self::assertEquals($withLang, $this->captcha->renderJs('vi'));
        self::assertEquals($withCallback, $this->captcha->renderJs(null, true, 'myOnloadCallback'));
    }

    public function testDisplay()
    {
        self::assertInstanceOf(NoCaptcha::class, $this->captcha);

        $simple = '<div data-sitekey="{site-key}" class="g-recaptcha"></div>';
        $withAttrs = '<div data-theme="light" data-sitekey="{site-key}" class="g-recaptcha"></div>';

        self::assertEquals($simple, $this->captcha->display());
        self::assertEquals($withAttrs, $this->captcha->display(['data-theme' => 'light']));
    }

    public function testdisplaySubmit()
    {
        self::assertInstanceOf(NoCaptcha::class, $this->captcha);

        $javascript = '<script>function onSubmittest(){document.getElementById("test").submit();}</script>';
        $simple = '<button data-callback="onSubmittest" data-sitekey="{site-key}" class="g-recaptcha"><span>submit</span></button>';
        $withAttrs = '<button data-theme="light" class="g-recaptcha 123" data-callback="onSubmittest" data-sitekey="{site-key}"><span>submit123</span></button>';

        self::assertEquals($simple . $javascript, $this->captcha->displaySubmit('test'));
        $withAttrsResult = $this->captcha->displaySubmit('test','submit123',['data-theme' => 'light', 'class' => '123']);
        self::assertEquals($withAttrs . $javascript, $withAttrsResult);
    }

    public function testdisplaySubmitWithCustomCallback()
    {
        self::assertInstanceOf(NoCaptcha::class, $this->captcha);

        $withAttrs = '<button data-theme="light" class="g-recaptcha 123" data-callback="onSubmitCustomCallback" data-sitekey="{site-key}"><span>submit123</span></button>';

        $withAttrsResult = $this->captcha->displaySubmit('test-custom','submit123',['data-theme' => 'light', 'class' => '123', 'data-callback' => 'onSubmitCustomCallback']);
        self::assertEquals($withAttrs, $withAttrsResult);
    }
}
