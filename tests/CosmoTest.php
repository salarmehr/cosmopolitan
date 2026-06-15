<?php
use PHPUnit\Framework\TestCase;
use Miloun\Cosmo\Bundle;
use Miloun\Cosmo\Cosmo;

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
        $actual = cosmo('tu')->unit('temperature', 'celsius', 26, 'short');
        $this->assertEquals('26°C', $actual);
    }

    public function testPercentage()
    {
        $actual = new Cosmo('en_AU')->percentage(.2);
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
        $actual = new Cosmo('en_AU')->get(Bundle::LOCALE, 'listPattern')->get('standard')->get('end');
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
        $actual = new Cosmo('en_AU')->currency('aud');
        $this->assertEquals('Australian Dollar', $actual);

        $actual = new Cosmo('en_AU')->currency('aud', true);
        $this->assertEquals('$', $actual);

        // Standard (disambiguated) symbol, not the ambiguous narrow form: AUD in en_US is "A$", not "$".
        $this->assertEquals('A$', new Cosmo('en_US')->currency('aud', true));

        $this->expectException(Exception::class);
        new Cosmo('en_AU')->currency('foo', false, true);
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
    public function testMoney($expected, $local, $value, $currency, $precision)
    {
        $actual = new Cosmo($local)->money($value, $currency, $precision);
        $this->assertEquals($expected, $actual);
    }

    public function testMoneyNoCurrencyReturnsEmpty()
    {
        $this->assertEquals('', new Cosmo('en')->money(12.3));
    }

    public function testMoneyNoCurrencyStrictThrows()
    {
        $this->expectException(Exception::class);
        new Cosmo('en')->money(12.3, strict: true);
    }

    public function symbolProvider()
    {
        return [
            ['fa', NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, '٫'],
            ['fa', NumberFormatter::DIGIT_SYMBOL, '#'],
            ['fa', 'decimal_separator', '٫'],
            ['fa', 'grouping_separator', '٬'],
            ['fa', 'pattern_separator', ';'],
            ['fa', 'percent', '٪'],
            ['fa', 'zero_digit', '۰'],
            ['fa', 'digit', '#'],
            ['fa', 'minus_sign', '‎−'],
            ['fa', 'plus_sign', '‎+'],
            ['fa', 'currency', '¤'],
            ['fa', 'intl_currency', '¤¤'],
            ['fa', 'monetary_separator', '٫'],
            ['fa', 'exponential', '×۱۰^'],
            ['fa', 'permill', '؉'],
            ['fa', 'pad_escape', '*'],
            ['fa', 'infinity', '∞'],
            ['fa', 'nan', 'ناعدد'],
            ['fa', 'significant_digit', '@'],
            ['fa', 'monetary_grouping_separator', '٬'],
        ];
    }

    /**
     * @dataProvider symbolProvider
     * @throws Exception
     */
    public function testSymbol($local, $symbolName, $expected)
    {
        $actual = new Cosmo($local)->symbol($symbolName);
        $this->assertEquals($expected, $actual);
    }

    public function testSymbolInvalidNameThrows()
    {
        $this->expectException(Exception::class);
        new Cosmo('en')->symbol('not_a_real_symbol');
    }

    public function testOrdinal()
    {
        $actual = new Cosmo('en_AU')->ordinal(1);
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
        $actual = new Cosmo('en_US')->duration(1222060);
        $this->assertEquals('339:27:40', $actual);

        $actual = new Cosmo('en_US')->duration(1222060, true);
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
        $actual = new Cosmo($locale)->unit($unit, $scale, $value, $type);
        $this->assertEquals($expected, $actual);
    }

    public function directionProvider()
    {
        return [
            ['fa', 'rtl'],
            ['en', 'ltr'],
            // Minority RTL scripts with no locale-level layout data in ICU —
            // resolved via likely script + IntlChar bidi properties.
            ['dv', 'rtl'],   // Dhivehi (Thaana)
            ['nqo', 'rtl'],  // N'Ko
            // An explicit script subtag wins over the language's likely script.
            ['ar_Latn', 'ltr'],
            ['az_IQ', 'rtl'], // region-qualified likely subtags: az is Latin, az_IQ Arabic
        ];
    }

    /**
     * @dataProvider directionProvider
     */
    public function testDirection($locale, $expected)
    {
        $actual = new Cosmo($locale)->direction();
        $this->assertEquals($expected, $actual);
    }

    public function testLikelySubtags()
    {
        // Values mirror ICU's uloc_addLikelySubtags / uloc_minimizeSubtags output.
        $this->assertSame('en_Latn_US', new Cosmo('en')->addLikelySubtags()->locale);
        $this->assertSame('zh_Hant_TW', new Cosmo('zh_TW')->addLikelySubtags()->locale);
        $this->assertSame('dv_Thaa_MV', new Cosmo('dv')->addLikelySubtags()->locale);
        $this->assertSame('az_Arab_IQ', new Cosmo('az_IQ')->addLikelySubtags()->locale);
        $this->assertSame('ar_Latn_EG', new Cosmo('ar_Latn')->addLikelySubtags()->locale); // explicit script kept
        $this->assertSame('en', new Cosmo('en_Latn_US')->removeLikelySubtags()->locale);
        $this->assertSame('zh_TW', new Cosmo('zh_Hant_TW')->removeLikelySubtags()->locale);
        $this->assertSame('sr_ME', new Cosmo('sr_Latn_ME')->removeLikelySubtags()->locale);
        // Keywords survive the round trip.
        $this->assertSame('fa_Arab_IR@calendar=persian', new Cosmo('fa_IR@calendar=persian')->addLikelySubtags()->locale);
        // Unknown language: returned unchanged.
        $this->assertSame('xx', new Cosmo('xx')->addLikelySubtags()->locale);
    }

    public function testMoment(){
        $actual = new Cosmo('en_US')->moment(strtotime('2020/02/02'),'full');
        $this->assertEquals('Sunday, February 2, 2020 at 12:00 AM', $actual);
    }

    public function testZaman(){
        $actual = new Cosmo('en_US')->formatMoment(strtotime('2020/02/02'),'YYYY');
        $this->assertEquals('2020', $actual);
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

    // ---- cosmo-js parity methods ----

    public function testCompareAndSort()
    {
        $cosmo = new Cosmo('en');
        $this->assertLessThan(0, $cosmo->compare('a', 'b'));
        $this->assertGreaterThan(0, $cosmo->compare('b', 'a'));
        $this->assertSame(0, $cosmo->compare('a', 'a'));

        $this->assertSame(['apple', 'banana', 'cherry'], $cosmo->sort(['cherry', 'apple', 'banana']));
        // Swedish sorts å/ä/ö after z.
        $this->assertSame(['ar', 'zebra', 'år'], new Cosmo('sv')->sort(['år', 'zebra', 'ar']));

        $items = [['n' => 'b'], ['n' => 'a']];
        $this->assertSame([['n' => 'a'], ['n' => 'b']], $cosmo->sort($items, fn($i) => $i['n']));
    }

    public function testContains()
    {
        $cosmo = new Cosmo('en');
        $this->assertTrue($cosmo->contains('Café society', 'cafe'));        // base: ignore case & accent
        $this->assertTrue($cosmo->contains('Hello', 'ELL'));
        $this->assertFalse($cosmo->contains('Café', 'cafe', 'variant'));    // exact: case & accent matter
        $this->assertFalse($cosmo->contains('café', 'cafe', 'accent'));     // accent matters, case ignored
        $this->assertTrue($cosmo->contains('anything', ''));                // empty needle
        $this->assertFalse($cosmo->contains('abc', 'xyz'));
    }

    public function testSegmentation()
    {
        $cosmo = new Cosmo('en');
        $this->assertSame(['Hello', 'world', 'foo'], $cosmo->splitWords('Hello, world! foo'));
        $this->assertSame(['Hi there.', 'How are you?'], $cosmo->splitSentences('Hi there. How are you?'));
        $this->assertSame('The quick…', $cosmo->ellipsize('The quick brown fox', 12));
        $this->assertSame('Short', $cosmo->ellipsize('Short', 20));
    }

    public function testCase()
    {
        $this->assertSame('İSTANBUL', new Cosmo('tr')->upper('istanbul')); // Turkish dotted capital I
        $this->assertSame('ISTANBUL', new Cosmo('en')->upper('istanbul'));
        $this->assertSame('hello', new Cosmo('en')->lower('HELLO'));
    }

    public function testPluralCategory()
    {
        $en = new Cosmo('en');
        $this->assertSame('one', $en->pluralCategory(1));
        $this->assertSame('other', $en->pluralCategory(2));
        $this->assertSame('two', $en->pluralCategory(2, true));   // ordinal: 2nd
        $this->assertSame('zero', new Cosmo('ar')->pluralCategory(0));
    }

    public function testWeekInfo()
    {
        $this->assertSame(['firstDay' => 7, 'weekend' => [6, 7], 'minimalDays' => 1], new Cosmo('en_US')->weekInfo());
        $this->assertSame(1, new Cosmo('en_GB')->weekInfo()['firstDay']);   // Monday
        $this->assertSame([5, 6], new Cosmo('ar_EG')->weekInfo()['weekend']); // Fri/Sat
    }

    public function testMonthAndWeekdayNames()
    {
        $en = new Cosmo('en');
        $this->assertSame('January', $en->monthNames()[0]);
        $this->assertSame('Dec', $en->monthNames('medium')[11]);
        $this->assertCount(12, $en->monthNames());

        $this->assertSame('Sunday', $en->weekdayNames()[0]); // Sunday-first
        $this->assertSame('Sat', $en->weekdayNames('medium')[6]);

        // Persian calendar: month names follow the calendar's own ordinal.
        $this->assertSame('فروردین', new Cosmo('fa_IR')->monthNames()[0]);
    }

    public function testTimeZoneName()
    {
        $cosmo = new Cosmo('en', ['timeZone' => 'Australia/Sydney']);
        $this->assertStringContainsString('Australian Eastern', $cosmo->timeZoneName());
        // GMT+10 (AEST) or +11 (AEDT) depending on the season at call time.
        $this->assertContains($cosmo->timeZoneName('shortOffset'), ['GMT+10', 'GMT+11']);
    }

    public function testScientific()
    {
        $this->assertSame('1.2345E4', new Cosmo('en')->scientific(12345));
    }

    public function testCompact()
    {
        $en = new Cosmo('en');
        $this->assertSame('1.2K', $en->compact(1200));
        $this->assertSame('1.2 million', $en->compact(1200000, 'long'));
        $this->assertSame('1.2M', $en->compact(1200000, 'short'));
        $this->assertSame('1.2 million', $en->compact(1200000, 'full'));
        $this->assertSame('999', $en->compact(999));
        $this->assertSame('1B', $en->compact(1000000000));
    }

    public function testRelativeDuration()
    {
        $en = new Cosmo('en');
        // 'always' (default) — numeric word forms, matching the JS/Python ports.
        $this->assertSame('3 days ago', $en->relativeDuration(-3, 'day'));
        $this->assertSame('in 2 hours', $en->relativeDuration(2, 'hour'));
        $this->assertSame('in 1 quarter', $en->relativeDuration(1, 'quarter'));
        $this->assertSame('in 0 days', $en->relativeDuration(0, 'day'));
        $this->assertSame('in 5 minutes', $en->relativeDuration(5, 'minute'));
        // 'auto' — locale word forms where CLDR has them (a PHP feature Python lacks).
        $this->assertSame('yesterday', $en->relativeDuration(-1, 'day', 'auto'));
        $this->assertSame('tomorrow', $en->relativeDuration(1, 'day', 'auto'));
        $this->assertSame('today', $en->relativeDuration(0, 'day', 'auto'));
        // 'auto' with no word form falls back to numeric.
        $this->assertSame('2 days ago', $en->relativeDuration(-2, 'day', 'auto'));
        // Other locales.
        $this->assertSame('gestern', new Cosmo('de')->relativeDuration(-1, 'day', 'auto'));
        $this->assertSame('hier', new Cosmo('fr')->relativeDuration(-1, 'day', 'auto'));
        $this->expectException(\Miloun\Cosmo\CosmoException::class);
        $en->relativeDuration(1, 'fortnight');
    }

    public function testRelativeDurationBetween()
    {
        $en = new Cosmo('en');
        $now = 1580601600;
        $this->assertSame('in 5 days', $en->relativeDurationBetween($now + 5 * 86400, $now));
        $this->assertSame('3 days ago', $en->relativeDurationBetween($now - 3 * 86400, $now));
        $this->assertSame('yesterday', $en->relativeDurationBetween($now - 86400, $now));
        $this->assertSame('in 2 hours', $en->relativeDurationBetween($now + 7200, $now, 'always'));
    }

    public function testNumberRange()
    {
        $this->assertSame('3–5', new Cosmo('en')->numberRange(3, 5));
        $this->assertSame('1,000–2,000', new Cosmo('en')->numberRange(1000, 2000));
    }

    public function testMoneyRange()
    {
        $this->assertSame('$3.00–$5.00', new Cosmo('en_US')->moneyRange(3, 5));
        $this->assertSame('$3.00–$5.00', new Cosmo('en')->moneyRange(3, 5, 'USD'));
        // No currency available → empty string (no region, no explicit code).
        $this->assertSame('', new Cosmo('en')->moneyRange(3, 5));
    }

    public function testDateRange()
    {
        $t1 = gmmktime(0, 0, 0, 2, 2, 2020);
        $t2 = gmmktime(0, 0, 0, 2, 5, 2020);
        $t3 = gmmktime(0, 0, 0, 3, 2, 2020);
        $t4 = gmmktime(0, 0, 0, 2, 2, 2021);
        $en = new Cosmo('en_US', ['timeZone' => 'UTC']);
        // Elided date range — only the differing field repeats.
        $this->assertSame('Feb 2 – 5, 2020', $en->dateRange($t1, $t2));
        $this->assertSame('Feb 2 – Mar 2, 2020', $en->dateRange($t1, $t3));
        $this->assertSame('Feb 2, 2020 – Feb 2, 2021', $en->dateRange($t1, $t4));
        $this->assertSame('2/2/2020 – 2/5/2020', $en->dateRange($t1, $t2, 'short'));
        // Identical endpoints collapse to a single date.
        $this->assertSame('Feb 2, 2020', $en->dateRange($t1, $t1));
        // Time-only range.
        $th1 = gmmktime(15, 0, 0, 2, 2, 2020);
        $th2 = gmmktime(17, 30, 0, 2, 2, 2020);
        $this->assertSame('3:00 – 5:30 PM', $en->dateRange($th1, $th2, 'none', 'short'));
        // Non-Latin locale (Japanese, ～ separator) still works.
        $this->assertSame('2020年2月2日～5日', new Cosmo('ja', ['timeZone' => 'UTC'])->dateRange($t1, $t2));
        // long/full are unsupported (no CLDR interval data; not bound by ext-intl).
        $this->expectException(\Miloun\Cosmo\CosmoException::class);
        $en->dateRange($t1, $t2, 'long');
    }

    public function testJoin()
    {
        $en = new Cosmo('en');
        $this->assertSame('A, B, and C', $en->join(['A', 'B', 'C']));
        $this->assertSame('A, B, or C', $en->join(['A', 'B', 'C'], 'disjunction'));
        $this->assertSame('A and B', $en->join(['A', 'B']));
        $this->assertSame('only', $en->join(['only']));
        $this->assertSame('', $en->join([]));
        $this->assertSame('uno, dos y tres', new Cosmo('es')->join(['uno', 'dos', 'tres']));
    }

    public function testDisplayName()
    {
        $en = new Cosmo('en');
        $this->assertSame('French', $en->displayName('language', 'fr'));
        $this->assertSame('Japan', $en->displayName('region', 'JP'));
        $this->assertStringContainsString('Simplified', $en->displayName('script', 'Hans'));
        $this->assertSame('Buddhist Calendar', $en->displayName('calendar', 'buddhist'));
        $this->assertSame('Euro', $en->displayName('currency', 'EUR'));
        $this->expectException(Exception::class);
        $en->displayName('nope', 'x');
    }

    public function testSplitGraphemes()
    {
        $en = new Cosmo('en');
        $this->assertSame(['a', '👩‍👧', 'b'], $en->splitGraphemes('a👩‍👧b'));
        $this->assertSame([], $en->splitGraphemes(''));
    }

    public function testSupportedValues()
    {
        $en = new Cosmo('en');
        $this->assertContains('Australia/Sydney', $en->supportedValues('timeZone'));
        $this->assertContains('EUR', $en->supportedValues('currency'));
        $this->expectException(Exception::class);
        $en->supportedValues('calendar'); // not reachable through PHP's intl
    }

    public function testDurationMultiUnit()
    {
        $en = new Cosmo('en');
        $this->assertSame('3 hours, 5 minutes', $en->duration(['hours' => 3, 'minutes' => 5], true));
        $this->assertStringContainsString('2 days', $en->duration(['days' => 2, 'hours' => 3]));
        $this->assertSame('339:17:20', $en->duration(1221440)); // scalar seconds unchanged
        $this->assertSame('', $en->duration([]));
    }

    public function testNumberOptions()
    {
        $en = new Cosmo('en');
        $this->assertSame('2', $en->number(2.9, ['roundingMode' => 'floor', 'maximumFractionDigits' => 0]));
        $this->assertSame('3', $en->number(2.1, ['roundingMode' => 'ceil', 'maximumFractionDigits' => 0]));
        $this->assertSame('1.25', $en->number(1.23, ['roundingIncrement' => 5, 'minimumFractionDigits' => 2, 'maximumFractionDigits' => 2]));
        $this->assertSame('12345', $en->number(12345, ['useGrouping' => false]));
        $this->assertSame('$10.00', $en->money(9.991, 'USD', options: ['roundingMode' => 'ceil']));
        $this->assertSame('12.34%', $en->percentage(0.12349, 2, ['roundingMode' => 'floor']));
        // Default rounding is halfExpand (round half away from zero), matching the
        // JS port's Intl default — not ICU's native halfEven (which would give 12.34).
        $this->assertSame('12.35%', $en->percentage(0.12345, 2));
        $this->assertSame('$1.235', (new Cosmo('es_CL'))->money(1234.5, 'CLP'));
    }

    public function testCollationOptions()
    {
        $en = new Cosmo('en');
        $this->assertLessThan(0, $en->compare('item2', 'item10', ['numeric' => true]));
        $this->assertSame(['item1', 'item2', 'item10'], $en->sort(['item10', 'item2', 'item1'], null, ['numeric' => true]));
        $this->assertSame(['A', 'a', 'B', 'b'], $en->sort(['b', 'B', 'a', 'A'], null, ['caseFirst' => 'upper']));
    }

    public function testTransliterateAndRomanize()
    {
        $en = new Cosmo('en');
        $this->assertSame('Moskva', $en->romanize('Москва'));
        $this->assertSame('Lodz cafe', $en->transliterate('Łódź café', 'Any-Latin; Latin-ASCII'));
        $this->assertContains('Any-Latin', $en->supportedValues('transliterator'));
        $this->expectException(Exception::class);
        $en->transliterate('x', 'Nope-Nope');
    }

    public function testSpoofChecks()
    {
        $en = new Cosmo('en');
        $this->assertTrue($en->confusable('paypal', 'раураl')); // Cyrillic look-alike
        $this->assertFalse($en->confusable('hello', 'world'));
        $this->assertTrue($en->suspicious('pаypal')); // mixed Latin/Cyrillic
        $this->assertFalse($en->suspicious('paypal'));
    }

    public function testParsing()
    {
        $this->assertSame(1234.56, (new Cosmo('de'))->parseNumber('1.234,56'));
        $money = (new Cosmo('en_US'))->parseMoney('$12.30');
        $this->assertSame(['amount' => 12.3, 'currency' => 'USD'], $money);
        $utc = new Cosmo('en_US', ['timeZone' => 'UTC']);
        $this->assertSame(1580601600, $utc->parseMoment('2020-02-02', 'yyyy-MM-dd')->getTimestamp());
        $parsed = $utc->parseDate('February 2, 2020', 'long');
        $this->assertSame('February 2, 2020', $utc->date($parsed, 'long'));
        $this->expectException(Exception::class);
        (new Cosmo('en'))->parseNumber('not a number');
    }
}
