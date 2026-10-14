<?php

namespace App\MessageHandler;

use App\Entity\Lead;
use App\Entity\LeadImportError;
use App\Message\FetchMetaLeadMessage;
use App\Service\MetaLeadService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsMessageHandler]
final class FetchMetaLeadMessageHandler
{
    public function __construct(
        private readonly MetaLeadService $metaLeadService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        FetchMetaLeadMessage $message
    ): void {
        $leadId = trim($message->getLeadId());

        /*
         * 1. Sprawdzenie ID leada.
         */
        if ($leadId === '') {
            $this->logger->error(
                'Meta lead import failed: empty lead ID.'
            );

            throw new UnrecoverableMessageHandlingException(
                'Meta lead ID cannot be empty.'
            );
        }

        /*
         * 2. Sprawdzenie, czy lead nie został już wcześniej
         *    zaimportowany.
         *
         * Jest to bardzo ważne, ponieważ Meta może wysłać
         * ten sam webhook więcej niż jeden raz.
         */
        $existingLead = $this->entityManager
            ->getRepository(Lead::class)
            ->findOneBy([
                'metaLeadId' => $leadId,
            ]);

        if ($existingLead !== null) {
            $this->logger->info(
                'Meta lead already exists. Import skipped.',
                [
                    'meta_lead_id' => $leadId,
                ]
            );

            return;
        }

        /*
         * 3. Pobranie danych z Meta Graph API.
         */
        try {
            $metaData = $this->metaLeadService->getLead($leadId);
        } catch (
        ClientExceptionInterface |
        RedirectionExceptionInterface |
        DecodingExceptionInterface |
        TransportExceptionInterface $exception
        ) {
            /*
             * Błędy komunikacji z Meta.
             *
             * Nie oznaczamy ich jako permanentne.
             * Messenger może ponowić wiadomość.
             */
            $this->logger->error(
                'Temporary error while fetching Meta lead.',
                [
                    'meta_lead_id' => $leadId,
                    'exception' => $exception,
                ]
            );

            throw $exception;
        } catch (ServerExceptionInterface $exception) {
            /*
             * Błąd serwera Meta.
             *
             * To również może być błąd tymczasowy,
             * dlatego pozwalamy Messengerowi wykonać retry.
             */
            $this->logger->error(
                'Meta Graph API server error.',
                [
                    'meta_lead_id' => $leadId,
                    'exception' => $exception,
                ]
            );

            throw $exception;
        } catch (\Throwable $exception) {
            /*
             * Nieoczekiwany błąd podczas komunikacji z Meta.
             */
            $this->logger->critical(
                'Unexpected error while fetching Meta lead.',
                [
                    'meta_lead_id' => $leadId,
                    'exception' => $exception,
                ]
            );

            throw $exception;
        }

        /*
         * 4. Meta powinna zwrócić tablicę.
         */
        if (!is_array($metaData)) {
            $this->saveImportError(
                $leadId,
                'Meta API returned invalid data.',
                null
            );

            throw new UnrecoverableMessageHandlingException(
                'Meta API returned invalid data.'
            );
        }

        /*
         * 5. Sprawdzenie podstawowego ID zwróconego przez Meta.
         */
        $returnedLeadId = $metaData['id'] ?? null;

        if (
            !is_string($returnedLeadId)
            || trim($returnedLeadId) === ''
        ) {
            $this->saveImportError(
                $leadId,
                'Meta API response does not contain lead ID.',
                $metaData
            );

            throw new UnrecoverableMessageHandlingException(
                'Meta API response does not contain lead ID.'
            );
        }

        /*
         * 6. Sprawdzenie, czy Meta zwróciła ten sam lead,
         *    którego żądaliśmy.
         */
        if ((string) $returnedLeadId !== $leadId) {
            $this->saveImportError(
                $leadId,
                'Meta API returned a different lead ID.',
                $metaData
            );

            throw new UnrecoverableMessageHandlingException(
                'Meta API returned a different lead ID.'
            );
        }

        /*
         * 7. Ponowne sprawdzenie duplikatu.
         *
         * Przydatne np. gdy dwa workery przetworzą wiadomość
         * praktycznie równocześnie.
         */
        $existingLead = $this->entityManager
            ->getRepository(Lead::class)
            ->findOneBy([
                'metaLeadId' => $leadId,
            ]);

        if ($existingLead !== null) {
            $this->logger->info(
                'Meta lead already exists. Import skipped.',
                [
                    'meta_lead_id' => $leadId,
                ]
            );

            return;
        }

        /*
         * 8. Wyciągnięcie danych formularza.
         */
        $fieldData = $metaData['field_data'] ?? [];

        if (!is_array($fieldData)) {
            $this->saveImportError(
                $leadId,
                'Meta API returned invalid field_data.',
                $metaData
            );

            throw new UnrecoverableMessageHandlingException(
                'Meta API returned invalid field_data.'
            );
        }

        /*
         * 9. Zamiana field_data na prostą tablicę:
         *
         * [
         *     'full_name' => 'Jan Kowalski',
         *     'email' => 'jan@example.com',
         *     'phone_number' => '+48123456789'
         * ]
         */
        $fields = $this->extractFieldData($fieldData);

        /*
         * 10. Tworzymy encję Lead.
         */
        $lead = new Lead();

        /*
         * ID Mety.
         */
        $lead->setMetaLeadId($leadId);

        /*
         * Dane techniczne kampanii.
         */
        $lead->setFormId(
            $this->getStringValue(
                $metaData['form_id'] ?? null
            )
        );

        $lead->setPageId(
            $this->getStringValue(
                $metaData['page_id'] ?? null
            )
        );

        $lead->setAdId(
            $this->getStringValue(
                $metaData['ad_id'] ?? null
            )
        );

        $lead->setAdSetId(
            $this->getStringValue(
                $metaData['adset_id'] ?? null
            )
        );

        $lead->setCampaignId(
            $this->getStringValue(
                $metaData['campaign_id'] ?? null
            )
        );

        /*
         * Źródło leada.
         */
        $lead->setSource('META');

        /*
         * Pełny payload zapisujemy w JSON.
         *
         * Dzięki temu nie tracimy żadnych danych,
         * nawet jeśli Meta doda w przyszłości nowe pola.
         */
        $lead->setPayload($metaData);

        /*
         * Data utworzenia leada w Meta.
         */
        if (
            isset($metaData['created_time'])
            && is_string($metaData['created_time'])
        ) {
            try {
                $lead->setCreatedAt(
                    new \DateTimeImmutable(
                        $metaData['created_time']
                    )
                );
            } catch (\Throwable $exception) {
                /*
                 * Jeżeli data Mety jest niepoprawna,
                 * używamy aktualnej daty.
                 */
                $this->logger->warning(
                    'Invalid Meta lead created_time. Using current time.',
                    [
                        'meta_lead_id' => $leadId,
                        'created_time' => $metaData['created_time'],
                    ]
                );

                $lead->setCreatedAt(
                    new \DateTimeImmutable()
                );
            }
        } else {
            $lead->setCreatedAt(
                new \DateTimeImmutable()
            );
        }

        /*
         * Data poprawnego importu.
         */
        $lead->setImportedAt(
            new \DateTimeImmutable()
        );

        /*
         * 11. Zapis do bazy.
         */
        try {
            $this->entityManager->persist($lead);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            /*
             * Błąd bazy danych.
             *
             * Nie oznaczamy go jako permanentny.
             * Jeżeli problem jest chwilowy, Messenger
             * może ponowić wiadomość.
             */
            $this->logger->critical(
                'Database error while saving Meta lead.',
                [
                    'meta_lead_id' => $leadId,
                    'exception' => $exception,
                ]
            );

            throw $exception;
        }

        /*
         * 12. Sukces.
         */
        $this->logger->info(
            'Meta lead imported successfully.',
            [
                'meta_lead_id' => $leadId,
                'database_id' => $lead->getId(),
            ]
        );
    }

    /**
     * Zamienia Meta field_data na prostą tablicę.
     */
    private function extractFieldData(
        array $fieldData
    ): array {
        $fields = [];

        foreach ($fieldData as $field) {
            if (!is_array($field)) {
                continue;
            }

            $name = $field['name'] ?? null;
            $values = $field['values'] ?? [];

            if (
                !is_string($name)
                || $name === ''
                || !is_array($values)
                || $values === []
            ) {
                continue;
            }

            /*
             * Meta zazwyczaj zwraca pierwszą wartość
             * jako właściwą odpowiedź użytkownika.
             */
            $value = $values[0] ?? null;

            if (
                is_string($value)
                || is_numeric($value)
            ) {
                $fields[$name] = (string) $value;
            }
        }

        return $fields;
    }

    /**
     * Bezpieczne pobieranie wartości tekstowej.
     */
    private function getStringValue(
        mixed $value
    ): ?string {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        if (
            is_string($value)
            || is_numeric($value)
        ) {
            return (string) $value;
        }

        return null;
    }

    /**
     * Zapis błędu importu.
     */
    private function saveImportError(
        string $leadId,
        string $error,
        ?array $payload
    ): void {
        try {
            $importError = new LeadImportError();

            $importError->setMetaLeadId($leadId);
            $importError->setError($error);
            $importError->setPayload($payload);
            $importError->setCreatedAt(
                new \DateTimeImmutable()
            );

            $this->entityManager->persist(
                $importError
            );

            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            /*
             * Nie możemy pozwolić, aby błąd zapisu
             * LeadImportError ukrył pierwotny problem.
             */
            $this->logger->critical(
                'Could not save Meta lead import error.',
                [
                    'meta_lead_id' => $leadId,
                    'error' => $error,
                    'exception' => $exception,
                ]
            );
        }
    }
}
