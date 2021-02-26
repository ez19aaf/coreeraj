<?php

namespace Survey54\Reap\Domain;

use Survey54\Library\Domain\Domain;

class Log extends Domain
{
    public string $objectId;
    public string $objectType;
    public string $action;
    public array $request;
    public array $response;

    /**
     * Log constructor.
     * @param string $uuid
     * @param string $objectId
     * @param string $objectType
     * @param string $action
     * @param array $request
     * @param array $response
     * @param string|null $createdAt
     * @param string|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $objectId,
        string $objectType,
        string $action,
        array $request,
        array $response,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($uuid, $createdAt, $updatedAt);
        $this->objectId   = $objectId;
        $this->objectType = $objectType;
        $this->action     = $action;
        $this->request    = $request;
        $this->response   = $response;
    }

    public static function build(array $data, bool $buildByAlias = false): self
    {
        if ($buildByAlias) {
            $data = self::buildByAlias('log_', $data);
        }
        return new self(
            $data['uuid'],
            $data['objectId'],
            $data['objectType'],
            $data['action'],
            $data['request'],
            $data['response'],
            $data['createdAt'] ?? null,
            $data['updatedAt'] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'uuid'       => $this->uuid,
            'objectId'   => $this->objectId,
            'objectType' => $this->objectType,
            'action'     => $this->action,
            'request'    => $this->request,
            'response'   => $this->response,
            'createdAt'  => $this->createdAt,
            'updatedAt'  => $this->updatedAt,
        ];
    }
}
