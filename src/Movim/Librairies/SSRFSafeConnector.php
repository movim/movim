<?php

namespace Movim\Librairies;

use React\Socket\ConnectorInterface;

class SSRFSafeConnector implements ConnectorInterface
{
    // Lists found on https://docs.n8n.io/deploy/host-n8n/configure-n8n/security/enable-ssrf-protection#default-blocked-ranges
    private const EXTRA_BLOCKED_CIDRS_V4 = [
        ['169.254.0.0', 16], // Link-local
        ['192.0.0.0',   24], // IETF protocol assignments (RFC 6890)
        ['100.64.0.0',  10], // Carrier-grade NAT https://www.rfc-editor.org/info/rfc6598/#section-7
    ];
    private const EXTRA_BLOCKED_CIDRS_V6 = [
        ['fc00::', 7], // IPv6 unique local addresses
        ['fe80::', 10], // IPv6 link-local addresses
    ];

    public function __construct(
        private ConnectorInterface $connector,
        private array $domainsWhitelist = []
    ) {}

    public function connect($uri)
    {
        $host = parse_url('tls://' . $uri, PHP_URL_HOST); // adding tls:// to parse the URI correctly

        if (!empty($host) && !$this->isUriWhitelisted($uri)) {
            $ip = trim($host, '[]');
            if ($ip !== '' && $this->isPrivateIp($ip)) {
                $error = 'Blocked SSRF attempt to: ' . $ip . ' (' . $uri . ')';
                \logError($error);

                return \React\Promise\reject(
                    new \RuntimeException($error)
                );
            }
        }

        return $this->connector->connect($uri);
    }

    private function isPrivateIp(string $ip): bool
    {
        if (preg_match('/^::ffff:(\d+\.\d+\.\d+\.\d+)$/i', $ip, $matches)) {
            $ip = $matches[1];
        }

        // Basic ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }

        // Check ranges that filter_var misses
        $isIpv6 = str_contains($ip, ':');
        $cidrs  = $isIpv6 ? self::EXTRA_BLOCKED_CIDRS_V6 : self::EXTRA_BLOCKED_CIDRS_V4;

        foreach ($cidrs as [$range, $prefix]) {
            if ($this->ipInCidr($ip, $range, $prefix, $isIpv6)) {
                return true;
            }
        }

        return false;
    }

    private function ipInCidr(string $ip, string $range, int $prefix, bool $isIpv6): bool
    {
        $ipBin = inet_pton($ip);
        $rangeBin = inet_pton($range);

        if ($ipBin === false || $rangeBin === false) {
            return false;
        }

        $bytes = $isIpv6 ? 16 : 4;
        $mask  = str_repeat("\xff", intdiv($prefix, 8));

        $remainingBits = $prefix % 8;

        if ($remainingBits > 0) {
            $mask .= chr(0xff << (8 - $remainingBits) & 0xff);
        }

        $mask = str_pad($mask, $bytes, "\x00");

        return ($ipBin & $mask) === ($rangeBin & $mask);
    }

    private function isUriWhitelisted(string $uri): bool
    {
        $query = parse_url($uri, PHP_URL_QUERY);
        if ($query == null) return false;
        parse_str($query, $results);

        if (!empty($results['hostname'])) {
            foreach ($this->domainsWhitelist as $domain) {
                if (str_ends_with($domain, $results['hostname'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
