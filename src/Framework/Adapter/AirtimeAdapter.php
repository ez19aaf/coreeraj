<?php

namespace Survey54\Reap\Framework\Adapter;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Survey54\Library\Domain\Values\SwitchOn;
use Survey54\Reap\Framework\Exception\Error;

class AirtimeAdapter
{
    private Client $client;
    private string $token;
    private const ES_ORG_ID = '10602'; // EngageSpark Org ID

    /**
     * AirtimeAdapter constructor.
     * @param Client $client
     * @param string $token
     */
    public function __construct(Client $client, string $token)
    {
        $this->client = $client;
        $this->token  = $token;
    }

    /**
     * Pricing: https://www.engagespark.com/support/airtime-topup-transfer-api-global-pricing/
     * API Doc: https://www.engagespark.com/support/global-prepaid-airtime-topup-transfer-api
     * @param string $redemptionId
     * @param string $mobile
     * @param float $amount
     * @return array
     */
    public function topUp(string $redemptionId, string $mobile, float $amount): array
    {
        if ($_SERVER['SWITCH_AIRTIME'] !== SwitchOn::ON) {
            return [];
        }

        try {
            $options = [
                'headers' => [
                    'Authorization' => "Token $this->token",
                ],
                'json'    => [
                    'phoneNumber'    => $mobile,
                    'organizationId' => self::ES_ORG_ID,
                    'maxAmount'      => $amount,
                    'clientRef'      => "Survey54_$redemptionId",
                ],
            ];

            $response = $this->client->post('/v1/airtime-topup', $options);
            $data     = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (strtolower($data['status']) === 'success' && $response->getStatusCode() === 200) {
                return $data;
            }
            Error::throwError(Error::S54_TOP_UP_ERROR, (string)$response->getBody());
        } catch (BadResponseException $e) {
            Error::throwError(Error::S54_TOP_UP_ERROR, (string)$e->getResponse()->getBody());
        } catch (Exception $e) {
            Error::throwError(Error::S54_TOP_UP_ERROR, '{"error": "' . $e->getMessage() . '"}');
        }
        return [];
    }
}
