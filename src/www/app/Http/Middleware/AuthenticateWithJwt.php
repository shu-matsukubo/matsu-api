<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithJwt
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $payload = $this->verifyToken($token);
        } catch (\Throwable $exception) {
            Log::warning('JWT authentication failed', [
                'reason' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set('auth_sub', $payload['sub']);
        $request->attributes->set('auth_payload', $payload);

        return $next($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function verifyToken(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \RuntimeException('JWT must have three parts.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = $this->jsonDecode($this->base64UrlDecode($encodedHeader));
        $payload = $this->jsonDecode($this->base64UrlDecode($encodedPayload));

        if (($header['alg'] ?? null) !== 'RS256') {
            throw new \RuntimeException('Unexpected JWT alg.');
        }

        $kid = $header['kid'] ?? null;

        if (! is_string($kid) || $kid === '') {
            throw new \RuntimeException('JWT kid is missing.');
        }

        $jwk = $this->findJwk($kid);
        $publicKey = $this->jwkToPem($jwk);
        $signature = $this->base64UrlDecode($encodedSignature);
        $signingInput = $encodedHeader.'.'.$encodedPayload;
        $verified = openssl_verify($signingInput, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            throw new \RuntimeException('JWT signature verification failed.');
        }

        $this->assertClaims($payload);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonDecode(string $json): array
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function findJwk(string $kid): array
    {
        /** @var string $cacheStore */
        $cacheStore = config('auth_server.cache_store');
        $cache = Cache::store($cacheStore);

        /** @var int $cacheSeconds */
        $cacheSeconds = config('auth_server.jwks_cache_seconds');

        $jwks = $cache->remember('auth_server_jwks', $cacheSeconds, function (): array {
            try {
                /** @var string $jwksUrl */
                $jwksUrl = config('auth_server.jwks_url');

                $response = Http::timeout(5)
                    ->acceptJson()
                    ->get($jwksUrl)
                    ->throw()
                    ->json();

                if (! is_array($response)) {
                    throw new \RuntimeException('Invalid JWKS response.');
                }

                return $response;
            } catch (RequestException $exception) {
                throw new \RuntimeException('Failed to fetch JWKS.', 0, $exception);
            }
        });

        /** @var array<string, mixed> $jwks */
        $keys = $jwks['keys'] ?? [];

        if (! is_iterable($keys)) {
            throw new \RuntimeException('Invalid JWKS keys.');
        }

        foreach ($keys as $key) {
            /** @var array<string, mixed> $key */
            if (($key['kid'] ?? null) === $kid) {
                return $key;
            }
        }

        $cache->forget('auth_server_jwks');
        throw new \RuntimeException('JWK not found.');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertClaims(array $payload): void
    {
        $now = time();
        /** @var string $issuer */
        $issuer = config('auth_server.issuer');
        /** @var string $audience */
        $audience = config('auth_server.audience');

        if (($payload['iss'] ?? null) !== $issuer) {
            throw new \RuntimeException('Unexpected issuer.');
        }

        $tokenAudience = $payload['aud'] ?? null;
        $audienceOk = is_array($tokenAudience)
            ? in_array($audience, $tokenAudience, true)
            : $tokenAudience === $audience;

        if (! $audienceOk) {
            throw new \RuntimeException('Unexpected audience.');
        }

        if (($payload['token_use'] ?? null) !== 'access') {
            throw new \RuntimeException('Unexpected token use.');
        }

        if (! isset($payload['sub']) || ! is_string($payload['sub']) || $payload['sub'] === '') {
            throw new \RuntimeException('Subject is missing.');
        }

        if (! isset($payload['exp']) || ! is_numeric($payload['exp']) || (int) $payload['exp'] <= $now) {
            throw new \RuntimeException('Token expired.');
        }

        if (isset($payload['iat']) && is_numeric($payload['iat']) && (int) $payload['iat'] > $now + 60) {
            throw new \RuntimeException('Token issued in the future.');
        }
    }

    /**
     * @param  array<string, mixed>  $jwk
     */
    private function jwkToPem(array $jwk): string
    {
        if (($jwk['kty'] ?? null) !== 'RSA' || ! is_string($jwk['n'] ?? null) || ! is_string($jwk['e'] ?? null)) {
            throw new \RuntimeException('Unsupported JWK.');
        }

        $modulus = $this->base64UrlDecode($jwk['n']);
        $exponent = $this->base64UrlDecode($jwk['e']);
        $rsaPublicKey = $this->asn1Sequence(
            $this->asn1Integer($modulus)
            .$this->asn1Integer($exponent)
        );
        $algorithmIdentifier = $this->asn1Sequence(
            $this->asn1ObjectIdentifier("\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01")
            ."\x05\x00"
        );
        $subjectPublicKeyInfo = $this->asn1Sequence(
            $algorithmIdentifier
            ."\x03".$this->asn1Length(strlen($rsaPublicKey) + 1)."\x00".$rsaPublicKey
        );

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    private function base64UrlDecode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64url value.');
        }

        return $decoded;
    }

    private function asn1Sequence(string $value): string
    {
        return "\x30".$this->asn1Length(strlen($value)).$value;
    }

    private function asn1Integer(string $value): string
    {
        $value = ltrim($value, "\x00");

        if ($value === '') {
            $value = "\x00";
        }

        if ((ord($value[0]) & 0x80) !== 0) {
            $value = "\x00".$value;
        }

        return "\x02".$this->asn1Length(strlen($value)).$value;
    }

    private function asn1ObjectIdentifier(string $value): string
    {
        return "\x06".$this->asn1Length(strlen($value)).$value;
    }

    private function asn1Length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = '';

        while ($length > 0) {
            $bytes = chr($length & 0xFF).$bytes;
            $length >>= 8;
        }

        return chr(0x80 | strlen($bytes)).$bytes;
    }
}
