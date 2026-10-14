<?php

namespace App\Controller;

use App\Message\FetchMetaLeadMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class MetaWebhookController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly string $metaVerifyToken,
        private readonly string $metaAppSecret,
    ) {
    }

    /**
     * Weryfikacja webhooka przez Meta.
     */
    #[Route(
        '/webhook/meta',
        name: 'meta_webhook_verify',
        methods: ['GET']
    )]
    public function verify(Request $request): Response
    {
        $mode = $request->query->get('hub_mode');
        $token = $request->query->get('hub_verify_token');
        $challenge = $request->query->get('hub_challenge');

        if (
            $mode === 'subscribe'
            && $token === $this->metaVerifyToken
        ) {
            return new Response(
                $challenge,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/plain',
                ]
            );
        }

        return new Response(
            'Invalid verification token',
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Odbieranie webhooków z Meta.
     */
    #[Route(
        '/webhook/meta',
        name: 'meta_webhook',
        methods: ['POST']
    )]
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();

        if ($payload === '') {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Empty payload',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode(
            $payload,
            true
        );

        if (!is_array($data)) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Invalid JSON',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        /*
         * Meta wysyła webhook dotyczący Page.
         */
        if (($data['object'] ?? null) !== 'page') {
            return new JsonResponse(
                [
                    'status' => 'ignored',
                ],
                Response::HTTP_OK
            );
        }

        foreach ($data['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {

                /*
                 * Interesują nas tylko leadgen.
                 */
                if (($change['field'] ?? null) !== 'leadgen') {
                    continue;
                }

                $value = $change['value'] ?? [];

                $leadId = $value['leadgen_id'] ?? null;

                if (!$leadId) {
                    continue;
                }

                /*
                 * Wrzucamy ID leada do Messengera.
                 *
                 * Nie pobieramy tutaj danych z Meta.
                 */
                $this->messageBus->dispatch(
                    new FetchMetaLeadMessage(
                        (string) $leadId
                    )
                );
            }
        }

        /*
         * Meta oczekuje szybkiej odpowiedzi.
         */
        return new JsonResponse(
            [
                'status' => 'ok',
            ],
            Response::HTTP_OK
        );
    }
}
