<?php

use Movim\i18n\Locale;
use Respect\Validation\Validator;

function validateServerNode($server, $node)
{
    return validateServer($server) && validateNode($node);
}

function validateServer($server)
{
    return (Validator::stringType()->noWhitespace()->length(6, 64)->isValid($server));
}

function validateNode($node)
{
    return (Validator::stringType()->length(2, 256)->isValid($node));
}

function validateTag($tag)
{
    return Validator::stringType()->notEmpty()->isValid($tag);
}

function validateForm($data)
{
    return Validator::in(array_keys(Locale::getList()))->isValid($data->language->value);
}

// RFC 7622: https://www.rfc-editor.org/rfc/rfc7622
// RFC 7613: https://www.rfc-editor.org/rfc/rfc7613
// RFC 5891: https://www.rfc-editor.org/rfc/rfc5891
// RFC 8264: https://www.rfc-editor.org/rfc/rfc8264

function validateJid(string $jid): bool
{
    // Databases limitations
    if (!Validator::stringType()->length(6, 255)->isValid($jid)) return false;

    // RFC 7622 §3.1: resource separator is the first '/' after the domainpart
    $resource = null;
    $inBracket = false;

    for ($i = 0, $len = strlen($jid); $i < $len; $i++) {
        if ($jid[$i] === '[') $inBracket = true;
        elseif ($jid[$i] === ']') $inBracket = false;
        elseif ($jid[$i] === '/' && !$inBracket) {
            $resource = substr($jid, $i + 1);
            $jid = substr($jid, 0, $i);
            break;
        }
    }

    // RFC 7622 §3.1: localpart separator is the last '@'
    $local = null;

    if (($atPos = strrpos($jid, '@')) !== false) {
        $local = substr($jid, 0, $atPos);
        $domain = substr($jid, $atPos + 1);
    } else {
        $domain = $jid;
    }

    return validateLocal($local)
        && validateDomain($domain)
        && validateResource($resource);
}

function validateLocal(?string $local): bool
{
    if ($local === null) return true;

    // RFC 7613 §2.3 (UsernameCaseMapped): width mapping then case folding
    // RFC 8264 §5.2.1: fullwidth/halfwidth variants mapped to ASCII equivalents
    $local = mb_strtolower(strtr($local, array_combine(
        array_map('mb_chr', range(0xFF01, 0xFF5E)),
        array_map('chr', range(0x21, 0x7E))
    )), 'UTF-8');

    // RFC 7622 §3.3: localpart 1–1023 bytes
    if ($local === '' || strlen($local) > 1023) return false;

    // RFC 7622 §3.3.1: these characters are explicitly forbidden in localpart
    if (preg_match('/["&\'\/:<>@]/', $local)) return false;

    // RFC 7613 §2.3 + RFC 8264 §4.2: PRECIS IdentifierClass rules
    return validatePrecisIdentifier($local);
}

function validateDomain(string $domain): bool
{
    // RFC 7622 §3.2: domainpart 1–1023 bytes
    if ($domain === '' || strlen($domain) > 1023) return false;

    // RFC 7622 §3.2: IP literals and IPv4 addresses are valid domainparts
    if (preg_match('/^\[.*\]$/', $domain) || filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return true;
    }

    // RFC 5891 §5: IDNA2008 conversion via UTS#46
    // RFC 5891 §4.1: check bidi and ContextJ rules
    $ace = function_exists('idn_to_ascii')
        ? idn_to_ascii(
            rtrim($domain, '.'),
            IDNA_NONTRANSITIONAL_TO_ASCII | IDNA_CHECK_BIDI | IDNA_CHECK_CONTEXTJ,
            INTL_IDNA_VARIANT_UTS46,
            $info
        )
        : (preg_match('/^[a-zA-Z0-9.\-]+$/', $domain) ? $domain : false);

    if ($ace === false || (isset($info['errors']) && $info['errors'])) return false;

    // RFC 1035 §2.3.4 (as referenced by RFC 5891 §5.5): total domain ≤ 253 octets
    if (strlen($ace) > 253) return false;

    foreach (explode('.', $ace) as $label) {
        // RFC 5891 §5.5: each label 1–63 octets, LDH only, no leading/trailing hyphen
        if ($label === '' || strlen($label) > 63) return false;
        if (!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?$|^[a-zA-Z0-9]$/', $label)) return false;
    }

    return true;
}

function validateResource(?string $resource): bool
{
    if ($resource === null) return true;

    // RFC 7622 §3.4: resourcepart 1–1023 bytes
    if ($resource === '' || strlen($resource) > 1023) return false;

    // RFC 7613 §2.2 + RFC 8264 §4.3: PRECIS FreeformClass rules (spaces allowed)
    return validatePrecisIdentifier($resource, freeform: true);
}

function validatePrecisIdentifier(string $s, bool $freeform = false): bool
{
    // RFC 8264 §4: validate UTF-8 encoding before iterating code points
    if (!mb_check_encoding($s, 'UTF-8')) return false;

    $i = 0;
    while ($i < strlen($s)) {
        $b = ord($s[$i]);
        if ($b < 0x80) {
            $cp = $b;
            $i += 1;
        } elseif (($b & 0xE0) === 0xC0) {
            $cp = (($b & 0x1F) << 6)  | (ord($s[$i + 1]) & 0x3F);
            $i += 2;
        } elseif (($b & 0xF0) === 0xE0) {
            $cp = (($b & 0x0F) << 12) | ((ord($s[$i + 1]) & 0x3F) << 6)  | (ord($s[$i + 2]) & 0x3F);
            $i += 3;
        } elseif (($b & 0xF8) === 0xF0) {
            $cp = (($b & 0x07) << 18) | ((ord($s[$i + 1]) & 0x3F) << 12) | ((ord($s[$i + 2]) & 0x3F) << 6) | (ord($s[$i + 3]) & 0x3F);
            $i += 4;
        } else return false;

        // RFC 8264 §9.13: C0 and C1 control characters are disallowed
        if ($cp <= 0x001F || $cp === 0x007F) return false;
        if ($cp >= 0x0080 && $cp <= 0x009F) return false;

        // RFC 8264 §9.3: surrogate code points are disallowed
        if ($cp >= 0xD800 && $cp <= 0xDFFF) return false;

        // RFC 8264 §9.4: non-characters are disallowed (U+nFFFE, U+nFFFF, U+FDD0–U+FDEF)
        if (($cp & 0xFFFF) >= 0xFFFE) return false;
        if ($cp >= 0xFDD0 && $cp <= 0xFDEF) return false;

        if (!$freeform) {
            // RFC 8264 §4.2 (IdentifierClass): space separators are disallowed
            // RFC 7613 §2.3: applies IdentifierClass to XMPP localpart
            static $spaces = [
                0x0020,
                0x00A0,
                0x1680,
                0x2000,
                0x2001,
                0x2002,
                0x2003,
                0x2004,
                0x2005,
                0x2006,
                0x2007,
                0x2008,
                0x2009,
                0x200A,
                0x202F,
                0x205F,
                0x3000
            ];
            if (in_array($cp, $spaces, true)) return false;
        }
    }

    return true;
}
