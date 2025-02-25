<?php

declare(strict_types=1);

namespace Omnipay\NestPay\Message;

class CancelResponse extends AbstractResponse
{
    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->data['OrderId'] ?? null;
    }
}
