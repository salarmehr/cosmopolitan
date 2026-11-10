<?php
declare(strict_types=1);

namespace Miloun\Cosmo;

use IntlChar;
use ResourceBundle;

/**
 * @internal Reads the installed ICU langInfo resource; no locale data is bundled.
 *
 * ICU serializes likely subtags as a BytesTrie plus packed language/script/region
 * values. Format references:
 * https://github.com/unicode-org/icu/blob/release-76-1/icu4c/source/common/unicode/bytestrie.h
 * https://github.com/unicode-org/icu/blob/release-76-1/icu4c/source/common/loclikelysubtags.cpp
 */
final class IcuLikelySubtags
{
    private function __construct(private readonly string $bytes) {}

    /** @return array<string, string> */
    public static function load(): array
    {
        $likely = ResourceBundle::create('langInfo', null, false)?->get('likely');
        $bytes = $likely?->get('trie');
        $numbers = $likely?->get('lsrnum');
        $m49 = $likely?->get('m49');
        if (!is_string($bytes) || !is_array($numbers) || !$m49 instanceof ResourceBundle) {
            throw new UnsupportedException('The installed ICU does not expose supported likely-subtag data.');
        }

        $reader = new self($bytes);
        $entries = [];
        $reader->node(0, '', $entries);
        $table = [];
        foreach ($entries as $key => $index) {
            // Value 1 means the language skips the script level of the trie.
            if ($index < 2) {
                continue;
            }
            $parts = [];
            $part = '';
            for ($i = 0; $i < strlen($key); ++$i) {
                $byte = ord($key[$i]);
                if ($byte === 42) {
                    $parts[] = '';
                } else {
                    $part .= chr($byte & 0x7f);
                    if ($byte & 0x80) {
                        $parts[] = $part;
                        $part = '';
                    }
                }
            }
            $language = $parts[0] ?: 'und';
            $languageKey = self::encode($parts[0]);
            if (($entries[$languageKey] ?? null) === 1) {
                array_splice($parts, 1, 0, ['']);
            }
            $lookup = implode('_', array_filter([$language, $parts[1] ?? '', $parts[2] ?? ''], static fn($v) => $v !== ''));
            $packed = $numbers[$index];
            $core = $packed & 0xffffff;
            $lang = $core % (27 ** 3);
            $name = chr(96 + $lang % 27) . chr(96 + intdiv($lang, 27) % 27);
            if (intdiv($lang, 27 ** 2) !== 0) {
                $name .= chr(96 + intdiv($lang, 27 ** 2));
            }
            $script = IntlChar::getPropertyValueName(IntlChar::PROPERTY_SCRIPT, ($packed >> 24) & 0xff, IntlChar::SHORT_PROPERTY_NAME);
            $region = intdiv($core, 27 ** 3) % (27 ** 2);
            $region = $region < 27 ? $m49->get($region)
                : chr(64 + $region % 27) . chr(64 + intdiv($region, 27));
            $table[$lookup] = $name . '_' . $script . '_' . $region;
        }
        return $table;
    }

    private static function encode(string $subtag): string
    {
        return $subtag === '' ? '*' : substr($subtag, 0, -1) . chr(ord(substr($subtag, -1)) | 0x80);
    }

    /** Decode one trie node, retaining intermediate values as well as leaves. */
    private function node(int $pos, string $key, array &$entries): void
    {
        $lead = ord($this->bytes[$pos++]);
        if ($lead < 0x10) {
            $length = ($lead === 0 ? ord($this->bytes[$pos++]) : $lead) + 1;
            $this->branch($pos, $length, $key, $entries);
        } elseif ($lead < 0x20) {
            $length = $lead - 0x10 + 1;
            $this->node($pos + $length, $key . substr($this->bytes, $pos, $length), $entries);
        } else {
            $entries[$key] = $this->value($pos, $lead >> 1);
            if (($lead & 1) === 0) {
                $this->node($pos, $key, $entries);
            }
        }
    }

    private function branch(int $pos, int $length, string $key, array &$entries): void
    {
        if ($length > 5) {
            ++$pos; // Binary branch pivot; both halves are visited.
            $delta = $this->delta($pos);
            $half = intdiv($length, 2);
            $this->branch($pos + $delta, $half, $key, $entries);
            $this->branch($pos, $length - $half, $key, $entries);
            return;
        }
        while ($length-- > 1) {
            $next = $key . $this->bytes[$pos++];
            $lead = ord($this->bytes[$pos++]);
            $value = $this->value($pos, $lead >> 1);
            if ($lead & 1) {
                $entries[$next] = $value;
            } else {
                $this->node($pos + $value, $next, $entries);
            }
        }
        $next = $key . $this->bytes[$pos++];
        $this->node($pos, $next, $entries);
    }

    private function value(int &$pos, int $lead): int
    {
        if ($lead < 0x51) {
            return $lead - 0x10;
        }
        if ($lead < 0x6c) {
            return (($lead - 0x51) << 8) | ord($this->bytes[$pos++]);
        }
        if ($lead < 0x7e) {
            return (($lead - 0x6c) << 16) | $this->integer($pos, 2);
        }
        return $this->integer($pos, $lead === 0x7e ? 3 : 4);
    }

    private function delta(int &$pos): int
    {
        $lead = ord($this->bytes[$pos++]);
        if ($lead < 0xc0) {
            return $lead;
        }
        if ($lead < 0xf0) {
            return (($lead - 0xc0) << 8) | ord($this->bytes[$pos++]);
        }
        if ($lead < 0xfe) {
            return (($lead - 0xf0) << 16) | $this->integer($pos, 2);
        }
        return $this->integer($pos, $lead === 0xfe ? 3 : 4);
    }

    private function integer(int &$pos, int $length): int
    {
        $value = 0;
        while ($length-- > 0) {
            $value = ($value << 8) | ord($this->bytes[$pos++]);
        }
        return $value;
    }
}
