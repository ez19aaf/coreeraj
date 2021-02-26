<?php

namespace Tests\Unit\Framework\Controller\Respondent;

use Exception;
use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Respondent\AddLsm;
use Tests\Unit\AbstractTestCase;

class AddLsmTest extends AbstractTestCase
{
    protected AddLsm $addLsm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addLsm = new AddLsm($this->respondentService);
    }

    public function testResponse(): void
    {
        $this->request->method('getAttribute')
            ->willReturn('xx.xx.xxx');
        $this->request->method('getParsedBody')
            ->willReturn(['i1', 'i2', 'i3']);

        $response = $this->addLsm->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }

    public function testWillThrowException(): void
    {
        $this->expectExceptionCode(400);
        $this->expectException(Exception::class);
        $data = ['i1', 'i2', 'i3'];
        $uuid = $this->respondent->uuid;
        $this->request->method('getAttribute')
            ->with('uuid')
            ->willReturn($uuid);
        $this->respondentService->method('addLsm')
            ->with($uuid, $data)
            ->willReturn([
                'value'   => 'i1',
                'summary' => 'good text',
            ]);
        $this->request->method('getParsedBody')
            ->willReturn(null);
        $this->addLsm->execute($this->request);
    }
}
