<?php

namespace Survey54\Reap\Framework\Adapter;

use AfricasTalking\SDK\AfricasTalking;
use Exception;
use Survey54\Reap\Framework\Exception\Error;

class AfricaTalkingAdapter
{
    private AfricasTalking $africaTalking;

    /**
     * AfricaTalkingAdapter constructor.
     * @param AfricasTalking $africaTalking
     */
    public function __construct(AfricasTalking $africaTalking)
    {
        $this->africaTalking = $africaTalking;
    }

    /**
     * Send bulk SMS messages
     * @param string $from
     * @param string $message
     * @param string $toNumbers (comma separated, to send to multiple numbers)
     * @return array
     */
    public function sendSMS(string $from, string $message, string $toNumbers): array
    {
        $sms = $this->africaTalking->sms();

        try {
            $response = $sms->send([
                'from'    => $from,
                'to'      => $toNumbers,
                'message' => $message,
            ]);
            if (strtolower($response['status']) === 'success') {
                return $response;
            }
            Error::throwError(Error::S54_AT_ERROR_SENDING_MESSAGE, json_encode($response, JSON_THROW_ON_ERROR));
        } catch (Exception $e) {
            Error::throwError(Error::S54_AT_ERROR_SENDING_MESSAGE, '{"error": "' . $e->getMessage() . '"}');
        }
        return [];
    }
}
