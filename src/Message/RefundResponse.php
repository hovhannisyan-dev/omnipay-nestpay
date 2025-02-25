<?php

declare(strict_types=1);

namespace Omnipay\NestPay\Message;

class RefundResponse extends AbstractResponse
{
    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->data['OrderId'] ?? null;
    }
}
