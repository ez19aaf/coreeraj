<?php


namespace Tests\Unit\Framework\Controller\Response;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Response\PostResponseUssd;
use Tests\Unit\AbstractTestCase;

class PostResponseUSSDTest extends AbstractTestCase
{
    protected PostResponseUssd $postResponseUSSD;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postResponseUSSD = new PostResponseUssd($this->responseService);
    }

    public function testWillReturnData(): void
    {
        $data = [];
        $this->request->method('getParsedBody')
            ->willReturn($data);
        $this->responseService->method('ussdResponse')
            ->with($data)
            ->willReturn('CON-Some question here?');

        $response = $this->postResponseUSSD->execute($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(200, $response->getStatusCode());
    }
}
