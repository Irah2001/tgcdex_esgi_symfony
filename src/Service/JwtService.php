<?php

namespace App\Service;

use App\Entity\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\Clock\SystemClock;
use DateTimeImmutable;
use Exception;

class JwtService
{
    private Configuration $config;

    public function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('ENn8txBYZolSD3d6YB3bCslFUI8vaLdmtPqF5a2ybTzaZrubytMp5Xo5AHksd6/mxILmEGjhHkoIPCu2NC2P9RSKDdlBvaFOWcIOpEbdSmraZ4CI9eHBlo0YVoSCVDbE9mejTLRU783oOTWV3NRAsf+FoAk/G377VlJ5Pu5nQ/GA3RU5R+OxT3J8wXNf570LMydOiPRldNzfzEiAeHeBZLOPPrt2QwkmTm4Jr3jtAuiPhFcpLVOfK/9DoSGsob8H1wFys2r3B7peYzZQXPYflURZeujx7o+hUvarQZhzvaING9dh415nePeyD4BIjuhQ/jD0NHbs2LRGwq4Us06/VnLAtowmeBlMGvjHmqsYFyE=')
        );
    }

    public function createToken(User $user): Plain
    {
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+1 hour');

        return $this->config->builder()
            ->issuedBy('Pokedex')
            ->permittedFor('Pokedex')
            ->identifiedBy(bin2hex(random_bytes(16)), true)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->withClaim('uid', $user->getId())
            ->getToken($this->config->signer(), $this->config->signingKey());
    }

    public function parseToken(string $token): ?Plain
    {
        try {
            $parsedToken = $this->config->parser()->parse($token);
            if (!$parsedToken instanceof Plain) {
                return null;
            }

            $constraints = [
                new IssuedBy('Pokedex'),
                new LooseValidAt(SystemClock::fromUTC()),
            ];

            if (!$this->config->validator()->validate($parsedToken, ...$constraints)) {
                return null;
            }

            return $parsedToken;
        } catch (Exception $e) {
            return null;
        }
    }
}
