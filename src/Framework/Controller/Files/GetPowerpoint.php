<?php

namespace Survey54\Reap\Framework\Controller\Files;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetPowerpoint extends FilesController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');
        $date     = date('Y/m/d');

        $file = $this->fileService->createPpt($surveyId);

        $fileName = $file['surveyTitle'] . "_Insights_$date.pptx";

        // For PowerPoint2007 and above .pptx files
        $this->response = $this->response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $this->response = $this->response->withHeader('Content-Disposition', "attachment; filename=$fileName");

        $stream = fopen($file['file'], 'rb+');

        $this->response->getBody()->write(fread($stream, (int)fstat($stream)['size']));
        return $this->response;
    }
}
