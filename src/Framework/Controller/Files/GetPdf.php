<?php

namespace Survey54\Reap\Framework\Controller\Files;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetPdf extends FilesController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');
        $date     = date('Y/m/d', time());

        $file     = $this->fileService->createPdf($surveyId);
        $fileName = $file['surveyTitle'] . "_Insights_$date.pdf";

        // For PDF
        $this->response = $this->response->withHeader('Content-Type', 'application/pdf');
        $this->response = $this->response->withHeader('Content-Disposition', "attachment; filename=$fileName");

        if (is_string($file['file']) && $stream = fopen($file['file'], 'rb+')) {
            $this->response->getBody()->write(fread($stream, (int)fstat($stream)['size']));
        }
        return $this->response;
    }
}
