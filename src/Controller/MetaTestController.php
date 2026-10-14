<?php

namespace App\Controller;

use App\Message\FetchMetaLeadMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class MetaTestController extends AbstractController
{
    #[Route('/meta/test/{leadId}', name: 'meta_test')]
    public function test(
        string $leadId,
        MessageBusInterface $messageBus
    ): Response {
        try {
            $messageBus->dispatch(
                new FetchMetaLeadMessage($leadId)
            );
        } catch (ExceptionInterface $e) {
            dd($e->getMessage());
        }

        return new Response(
            'Lead został dodany do kolejki: ' . $leadId
        );
    }
}
