<?php

namespace App\Service;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Atrapa HttpClientInterface używana WYŁĄCZNIE do lokalnych testów.
 * Niezależnie od tego, jaki URL i jakie parametry (access_token, leadId itd.)
 * zostaną przekazane do request(), zawsze zwraca zawartość podanego pliku JSON
 * jako odpowiedź HTTP 200. Dzięki temu MetaLeadService w ogóle nie łączy się
 * z prawdziwym Graph API - idealne do sprawdzenia całego łańcucha
 * Controller -> Message -> Handler bez realnego leadgen_id i tokenu strony.
 */
class FixtureMetaHttpClient extends MockHttpClient
{
    public function __construct(string $fixturePath)
    {
        if (!is_file($fixturePath)) {
            throw new \RuntimeException(sprintf(
                'Plik z przykładowymi danymi nie istnieje: %s',
                $fixturePath
            ));
        }

        $body = file_get_contents($fixturePath);

        parent::__construct(static function () use ($body) {
            return new MockResponse($body, [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/json'],
            ]);
        });
    }
}
