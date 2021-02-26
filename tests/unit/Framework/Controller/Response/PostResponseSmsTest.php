<?php


namespace Tests\Unit\Framework\Controller\Response;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Response\PostResponseSms;
use Tests\Unit\AbstractTestCase;

class PostResponseSmsTest extends AbstractTestCase
{
    protected PostResponseSms $postResponseSms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postResponseSms = new PostResponseSms($this->responseService);
    }

    public function testWillReturnResponse(): void
    {
        $data = [
            'to'   => '+441727171727',
            'from' => '+441727171725',
            'text' => 'Hello there',
        ];
        $this->request->method('getParsedBody')
            ->willReturn($data);
        $this->responseService->method('smsResponse')
            ->with($data)
            ->willReturn($data);

        $response = $this->postResponseSms->execute($this->request);
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }
}
