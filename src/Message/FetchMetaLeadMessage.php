<?php

namespace App\Message;

final class FetchMetaLeadMessage
{
    public function __construct(
        private readonly string $leadId
    ) {
    }

    public function getLeadId(): string
    {
        return $this->leadId;
    }
}
