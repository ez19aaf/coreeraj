<?php


namespace Tests\Unit\Framework\Controller\Survey;


use Slim\Http\Response;
use Survey54\Reap\Framework\Controller\Survey\PostLaunchSmsUssdAud;
use Survey54\Reap\Framework\Validator\Survey\PostLaunchSmsUssdAudValidator;
use Tests\Unit\AbstractTestCase;

class PostLaunchSmsUssdTest extends AbstractTestCase
{
    protected PostLaunchSmsUssdAud $postLaunchSmsUSSD;
    protected PostLaunchSmsUssdAudValidator $postLaunchSmsUSSDValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postLaunchSmsUSSDValidator = $this->createMock(PostLaunchSmsUssdAudValidator::class);
        $this->postLaunchSmsUSSD          = new PostLaunchSmsUssdAud($this->surveyService, $this->postLaunchSmsUSSDValidator);
    }

    public function testWillReturnResponse(): void
    {
        $this->postLaunchSmsUSSDValidator->method('validate')
            ->willReturn(['shortCode' => '*121*2*1']);
        $this->surveyRepository->method('update')
            ->willReturn(true);
        $this->surveyRepository->method('find')
            ->willReturn($this->survey);
        $this->africasTalkingAdapter->method('sendSMS')
            ->willReturn([
                'to'      => '+447879981816',
                'from'    => '+447879981815',
                'message' => 'Hey there.',
            ]);
        $this->request->method('getAttribute')
            ->with('uuid')
            ->willReturn($this->survey->uuid);
        $response = $this->postLaunchSmsUSSD->execute($this->request);
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(202, $response->getStatusCode());
    }

}
