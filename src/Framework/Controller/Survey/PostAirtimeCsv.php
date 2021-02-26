<?php


namespace Survey54\Reap\Framework\Controller\Survey;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostAirtimeCsv extends SurveyController
{
    private string $projectRoot;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */

    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        $params    = $this->validator->validate($request);
        $numbers   = $params['numbers'];
        $chunked   = array_chunk($numbers, 40, false);
        $incentive = $params['incentive'];
        $country   = $params['country'];
        chdir($this->projectRoot);
        $exit   = '';
        $output = '';
        foreach ($chunked as $chunk) {
            $numbers = json_encode($chunk, JSON_THROW_ON_ERROR);
            $cmd     = "php src/console.php --task send-airtime-csv-numbers --data '{\"incentive\":$incentive, \"country\":\"$country\", \"numbers\":$numbers}' > /dev/null 2>&1 &";
            exec($cmd, $output, $exit);
        }
        return $this->response->withJson(['exitValue' => $exit], 202);
    }

    /**
     * @param string $projectRoot
     * @return PostAirtimeCsv
     */

    public function setProjectRoot(string $projectRoot): PostAirtimeCsv
    {
        $this->projectRoot = $projectRoot;
        return $this;
    }
}
