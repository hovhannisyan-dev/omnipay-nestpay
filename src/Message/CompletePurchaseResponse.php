<?php

declare(strict_types=1);

namespace Omnipay\NestPay\Message;

class CompletePurchaseResponse extends PurchaseResponse
{
    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->data['oid'] ?? null;
    }
}
