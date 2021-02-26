<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\DeactivateRespondent;
use Tests\Unit\AbstractTestCase;

class DeactivatePasswordTest extends AbstractTestCase
{
    protected DeactivateRespondent $deactivateRespondent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deactivateRespondent = new DeactivateRespondent($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->deactivateRespondent->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
