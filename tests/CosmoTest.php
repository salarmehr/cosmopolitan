<?php

namespace Salarmehr\Cosmopolitan;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/helper.php';

class CosmoTest extends TestCase
{
    public function languageProvider()
    {
        return [
            ['en', 'en', 'English'],
            ['en', 'en_AU', 'English'],
            ['fa', 'en', 'انگلیسی'],
            ['fa', 'fa', 'فارسی'],
        ];
    }

    /**
     * @dataProvider languageProvider
     */
    public function testLanguage($local, $language, $name)
    {
        $cosmo = new Cosmo($local);
        $this->assertEquals($cosmo->language($language), $name);
    }

    public function testHelper()
    {
        $actual = cosmo('tu')->unit('temperature', 'celsius', 26);
        $this->assertEquals('26°C', $actual);
    }

    public function testPercentage()
    {
        $actual = Cosmo::create('en_AU')->percentage(.2);
        $this->assertEquals('20%', $actual);
    }

    public function quoteProvider()
    {
        return [
            ['en', 'text', '“text”'],
            ['fa', 'text', '«text»'],
            ['ar', 'text', '”text“'],
            ['sp', 'text', '“text”'],
            ['ch', 'text', '“text”'],
            ['chi', 'text', '“text”'],
        ];
    }

    /**
     * @dataProvider quoteProvider
     */
    public function testQuote($local, $text, $quote)
    {
        $cosmo = new Cosmo($local);
        $this->assertEquals($cosmo->quote($text), $quote);
    }

    public function testGet()
    {
        $actual = Cosmo::create('en_AU')->get(Bundle::LOCALE, 'listPattern')->get('standard')->get('end');
        $this->assertEquals('{0} and {1}', $actual);
    }

    public function messageProvider()
    {
        return [
            ['en', 'aa {b} {c} dd', ['b' => 'bb', 'c' => 'cc'], 'aa bb cc dd'],
            [
                "en_US",
                "{0,number,integer} monkeys on {1,number,integer} trees make {2,number} monkeys per tree",
                [4560, 123, 4560 / 123],
                '4,560 monkeys on 123 trees make 37.073 monkeys per tree',
            ],
            [
                "de",
                "{0,number,integer} Affen auf {1,number,integer} Bäumen sind {2,number} Affen pro Baum",
                [4560, 123, 4560 / 123],
                '4.560 Affen auf 123 Bäumen sind 37,073 Affen pro Baum',
            ],
        ];
    }

    /**
     * @dataProvider messageProvider
     */
    public function testMessage($local, $text, $arguments, $message)
    {
        $cosmo = new Cosmo($local);
        $this->assertEquals($cosmo->message($text, $arguments), $message);
    }

    public function testCurrency()
    {
        $actual = Cosmo::create('en_AU')->currency('aud');
        $this->assertEquals('Australian Dollar', $actual);

        $actual = Cosmo::create('en_AU')->currency('aud', true);
        $this->assertEquals('$', $actual);

        $this->expectException(Exception::class);
        Cosmo::create('en_AU')->currency('foo', false, true);
    }

    public function moneyProvider()
    {
        return [
            ['$12.30', 'en_AU', 12.3, 'AUD', null],
            ['$12.30', 'en_AU', 12.3, null, null],
            ['$12.30', 'en_AU', 12.3, 'AUD', null],
            ['A$12.30', 'en_US', 12.3, 'AUD', null],
            ['$12.32', 'en_AU', 12.32342, 'AUD', null],
            ['$12', 'en_AU', 12, 'AUD', 0],
            ['$12', 'en_AU', 12.32342, 'AUD', 0],
            ['$13', 'en_AU', 12.62342, 'AUD', 0],
            ['‎ریال ۱۳', 'fa_IR', 12.62342, null, null],
            ['‎ریال ۱۲٫۶۲', 'fa_IR', 12.62342, null, 2],
        ];
    }

    /**
     * @dataProvider moneyProvider
     * @throws Exception
     */
    public function testMoney($expected, $local, $value, $currency, $precison)
    {
        $actual = Cosmo::create($local)->money($value, $currency, '', $precison);
        $this->assertEquals($expected, $actual);
    }

    public function testOrdinal()
    {
        $actual = Cosmo::create('en_AU')->ordinal(1);
        $this->assertEquals('1st', $actual);
    }

    public function countryProvider()
    {
        return [
            ['en', 'AU', 'Australia'],
            ['en_AU', 'AU', 'Australia'],
            ['fa', 'AU', 'استرالیا'],
            ['fa', '', ''],
            ['fa', null, ''],
        ];
    }

    /**
     * @dataProvider countryProvider
     */
    public function testCountry($locale, $countryCode, $countryName)
    {
        $cosmo = new Cosmo($locale);
        $this->assertEquals($cosmo->country($countryCode), $countryName);
    }

    public function calendarProvider()
    {
        return [
            ['en', 'persian', 'Persian Calendar'],
            ['en_AU', 'buddhist', 'Buddhist Calendar'],
            ['fa', 'buddhist', 'تقویم بودایی'],
            ['fa', '', ''],
        ];
    }

    /**
     * @dataProvider calendarProvider
     */
    public function testCalendar($locale, $calendarCode, $calendarName)
    {
        $cosmo = new Cosmo($locale);
        $this->assertEquals($cosmo->calendar($calendarCode), $calendarName);
    }

    public function testDuration()
    {
        $actual = Cosmo::create('en_US')->duration(1222060);
        $this->assertEquals('339:27:40', $actual);

        $actual = Cosmo::create('en_US')->duration(1222060, true);
        $this->assertEquals('339 hours, 27 minutes, 40 seconds', $actual);
    }

    public function unitProvider()
    {
        return [
            ['en', 'digital', 'megabit', 1, 'full', '1 megabit'],
            ['en', 'digital', 'megabit', 2, 'full', '2 megabits'],
            ['en', 'digital', 'megabit', 1, 'medium', '1 Mb'],
            ['en', 'digital', 'megabit', 1, 'short', '1Mb'],

            ['en', 'temperature', 'celsius', 1, 'full', '1 degree Celsius'],
            ['en', 'temperature', 'celsius', 2, 'full', '2 degrees Celsius'],
            ['en', 'temperature', 'celsius', 1, 'medium', '1°C'],
            ['en', 'temperature', 'celsius', 1, 'short', '1°C'],
        ];
    }

    /**
     * @dataProvider unitProvider
     */
    public function testUnit($locale, $unit, $scale, $value, $type, $expected)
    {
        $actual = Cosmo::create($locale)->unit($unit, $scale, $value, $type);
        $this->assertEquals($expected, $actual);
    }

    public function directionProvider()
    {
        return [
            ['fa', 'rtl'],
            ['en', 'ltr'],
        ];
    }

    /**
     * @dataProvider directionProvider
     */
    public function testDirection($locale, $expected)
    {
        $actual = Cosmo::create($locale)->direction($locale, $expected);
        $this->assertEquals($expected, $actual);
    }

    public function testMethodWithoutParameter()
    {
        $cosmo = new Cosmo('en_AU');
        $this->assertEquals('Australia', $cosmo->country());
        $this->assertEquals('', $cosmo->country(''));
        $this->assertEquals('', $cosmo->country(null));

        $this->assertEquals('English', $cosmo->language());
        $this->assertEquals('', $cosmo->language(''));
        $this->assertEquals('', $cosmo->language(null));

        $this->assertEquals('Australian Dollar', $cosmo->currency());
        $this->assertEquals('', $cosmo->currency(''));
        $this->assertEquals('', $cosmo->currency(null));

        $this->assertEquals('ltr', $cosmo->direction());

        $cosmo = new Cosmo('en');
        $this->assertEquals('', $cosmo->country());
        $this->assertEquals('', $cosmo->country(''));
        $this->assertEquals('', $cosmo->country(null));

        $this->assertEquals('English', $cosmo->language());
        $this->assertEquals('', $cosmo->language(''));
        $this->assertEquals('', $cosmo->language(null));

        $this->assertEquals('', $cosmo->currency());
        $this->assertEquals('', $cosmo->currency(''));
        $this->assertEquals('', $cosmo->currency(null));
        $this->assertEquals('', $cosmo->script());

        $cosmo = new Cosmo('en_Latn_AU');
        $this->assertEquals('Latin', $cosmo->script());
        $this->assertEquals('', $cosmo->script(''));

        $this->assertEquals('ltr', $cosmo->direction());
        $this->assertEquals('🇦🇺', $cosmo->flag());
    }
}