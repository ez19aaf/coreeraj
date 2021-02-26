<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\ActivateRespondent;
use Tests\Unit\AbstractTestCase;

class ActivateRespondentTest extends AbstractTestCase
{
    protected ActivateRespondent $activateRespondent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activateRespondent = new ActivateRespondent($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');

        $response = $this->activateRespondent->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
