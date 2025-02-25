<?php

declare(strict_types=1);

namespace Omnipay\NestPay\Message;

class FetchTransactionResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->getStatus() === 'Approved';
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->data['Response'] ?? null;
    }
}
