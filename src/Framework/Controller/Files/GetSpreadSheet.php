<?php

namespace Survey54\Reap\Framework\Controller\Files;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetSpreadSheet extends FilesController
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $surveyId = $request->getAttribute('uuid');

        $fileData = $this->fileService->createSpreadSheet($surveyId);
        $date     = date('Y/m/d');
        $fileName = $fileData['surveyTitle'] . "_Insights_$date.xlsx";

        // For Excel2007 and above .xlsx files
        $this->response = $this->response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response = $this->response->withHeader('Content-Disposition', "attachment; filename=$fileName");

        $stream = fopen($fileData['file'], 'rb+');

        $this->response->getBody()->write(fread($stream, (int)fstat($stream)['size']));
        return $this->response;
    }
}
