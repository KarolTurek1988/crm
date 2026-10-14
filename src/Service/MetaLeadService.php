<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MetaLeadService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $metaPageAccessToken,
        private readonly string $metaAppSecret,
        private readonly string $metaGraphVersion,
    ) {
    }

    public function getLead(string $leadId): array
    {
        $url = sprintf(
            'https://graph.facebook.com/%s/%s',
            $this->metaGraphVersion,
            $leadId
        );

        $response = $this->httpClient->request(
            'GET',
            $url,
            [
                'query' => [
                    'access_token' => $this->metaPageAccessToken,

                    'fields' => implode(',', [
                        'id',
                        'created_time',
                        'field_data',
                        'form_id',
                        'page_id',
                        'ad_id',
                        'adset_id',
                        'campaign_id',
                    ]),

                    'appsecret_proof' => hash_hmac(
                        'sha256',
                        $this->metaPageAccessToken,
                        $this->metaAppSecret
                    ),
                ],
            ]
        );

        return $response->toArray();
    }
}
