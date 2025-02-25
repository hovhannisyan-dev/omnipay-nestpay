<?php

declare(strict_types=1);

namespace Omnipay\NestPay\Message;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\ResponseInterface;
use DOMDocument;
use DOMElement;
use Exception;
use Omnipay\NestPay\RequestInterface;
use Omnipay\NestPay\Traits\ParametersTrait;
use Omnipay\NestPay\Traits\RequestTrait;

abstract class AbstractRequest extends BaseAbstractRequest implements RequestInterface
{
    use RequestTrait;
    use ParametersTrait;

    /** @var $root DOMElement */
    private DOMElement $root;

    /** @var DOMDocument */
    private DOMDocument $document;

    private string $action = "purchase";

    /** @var array */
    protected array $requestParams;

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->getParameter('clientId');
    }

    /**
     * @param string $value
     * @return AbstractRequest
     */
    public function setClientId(string $value): AbstractRequest
    {
        return $this->setParameter('clientId', $value);
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setAction(string $value): void
    {
        $this->action = $value;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->getParameter('username');
    }

    /**
     * @param string $value
     * @return AbstractRequest
     */
    public function setUserName(string $value): AbstractRequest
    {
        return $this->setParameter('username', $value);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->getParameter('password');
    }

    /**
     * @param string $value
     * @return AbstractRequest
     */
    public function setPassword(string $value): AbstractRequest
    {
        return $this->setParameter('password', $value);
    }

    /**
     * @return string|null
     */
    public function getInstallment(): ?string
    {
        return $this->getParameter('installment');
    }

    /**
     * @param string $value
     * @return AbstractRequest
     */
    public function setInstallment(string $value): AbstractRequest
    {
        return $this->setParameter('installment', $value);
    }

    /**
     * @return string
     */
    public function getBank(): string
    {
        return $this->getParameter('bank');
    }

    /**
     * @param string $value
     * @return AbstractRequest
     */
    public function setBank(string $value): AbstractRequest
    {
        return $this->setParameter('bank', $value);
    }

    /**
     * @return bool|null
     */
    public function getOrderStatusQuery(): ?bool
    {
        return $this->getParameter('order_status_query');
    }

    /**
     * @param bool $value
     *
     * @return \Omnipay\NestPay\Message\AbstractRequest
     */
    public function setOrderStatusQuery(bool $value): AbstractRequest
    {
        return $this->setParameter('order_status_query', $value);
    }

    /**
     * @return string
     */
    protected function getHttpMethod(): string
    {
        return 'POST';
    }


    /**
     * @param mixed $data
     * @return ResponseInterface|AbstractResponse
     * @throws InvalidResponseException
     */
    public function sendData($data): AbstractResponse|ResponseInterface
    {
        try {
            $processType = $this->getProcessType();
            if (!empty($processType)) {
                $data['Type'] = $processType;
            }
            $shipInfo = $data['ship'] ?? [];
            $billInfo = $data['bill'] ?? [];
            unset($data['ship'], $data['bill']);

            $this->document = new DOMDocument('1.0', 'UTF-8');
            $this->root = $this->document->createElement('CC5Request');
            foreach ($data as $id => $value) {
                $this->root->appendChild($this->document->createElement($id, (string)$value));
            }

            $extra = $this->document->createElement('Extra');

            if (!empty($this->getOrderStatusQuery())) {
                $extra->appendChild($this->document->createElement('ORDERSTATUS', 'QUERY'));
                $this->root->appendChild($extra);
            }

            $this->document->appendChild($this->root);
            $this->addShipAndBillToXml($shipInfo, $billInfo);
            $httpRequest = $this->httpClient->request(
                $this->getHttpMethod(),
                $this->getEndpoint(),
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                $this->document->saveXML()
            );

            $response = (string)$httpRequest->getBody()->getContents();

            return $this->response = $this->createResponse($response);
        } catch (Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * @return string|null
     */
    public function getCurrencyNumeric(): ?string
    {
        $number = parent::getCurrencyNumeric();
        if (!is_null($number)) {
            return str_pad($number, 3, '0', STR_PAD_LEFT);
        }

        return null;
    }

    /**
     * @param array $ship
     * @param array $bill
     *
     * @return void
     * @throws \DOMException
     */
    private function addShipAndBillToXml(array $ship, array $bill): void
    {
        if (count($ship) > 0 && count($bill) > 0) {
            $shipTo = $this->document->createElement('ShipTo');
            foreach ($ship as $id => $value) {
                $shipTo->appendChild($this->document->createElement($id, $value));
            }

            $this->root->appendChild($shipTo);

            $billTo = $this->document->createElement('BillTo');
            foreach ($bill as $id => $value) {
                $billTo->appendChild($this->document->createElement($id, $value));
            }

            $this->root->appendChild($billTo);
        }
    }

    /**
     * @return string[]
     */
    protected function getBillTo(): array
    {
        return [
            'Name' => '',
            'Street1' => '',
            'Street2' => '',
            'Street3' => '',
            'City' => '',
            'StateProv' => '',
            'PostalCode' => '',
            'Country' => '',
            'Company' => '',
            'TelVoice' => '',
        ];
    }

    /**
     * @return string[]
     */
    protected function getShipTo(): array
    {
        return [
            'Name' => '',
            'Street1' => '',
            'Street2' => '',
            'Street3' => '',
            'City' => '',
            'StateProv' => '',
            'PostalCode' => '',
            'Country' => ''
        ];
    }
}
