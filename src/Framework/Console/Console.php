<?php

namespace Survey54\Reap\Framework\Console;

use Commando\Command;
use Exception;
use Pimple\Container;
use Survey54\Library\Domain\PermAdmin;
use Survey54\Library\Domain\PermOrganization;
use Survey54\Library\Domain\PermRespondent;
use Survey54\Library\Domain\Values\UserType;
use Survey54\Library\Exception\ExtendedException;
use Survey54\Library\Shield\ShieldReserved;
use Survey54\Library\Token\TokenBuilder;
use Survey54\Library\Token\TokenInfo;
use Survey54\Reap\Application\SurveyService;

class Console
{
    private Command $cmd;
    private Container $container;

    /**
     * Console constructor.
     * @param Command $cmd
     * @param Container $container
     */
    public function __construct(Command $cmd, Container $container)
    {
        $this->cmd       = $cmd;
        $this->container = $container;
    }

    /**
     * Execution entry
     */
    public function run(): void
    {
        $start = microtime(true);

        try {
            $data = json_decode($this->cmd['data'] ?? '{}', true, 512, JSON_THROW_ON_ERROR);

            switch ($this->cmd['task']) {
                case 'get-token':
                    echo "\n>> Get token for user";
                    switch ($data['type']) {
                        case UserType::ADMIN:
                            $permission = PermAdmin::CHUNKS;
                            break;
                        case UserType::ORGANIZATION:
                            $permission = PermOrganization::CHUNKS;
                            break;
                        case UserType::RESPONDENT:
                            $permission = PermRespondent::CHUNKS;
                            break;
                        default:
                            die('Wrong User Type');
                    }
                    $permission = json_encode(str_replace(ShieldReserved::_SELF_USER, $data['userId'], $permission), JSON_THROW_ON_ERROR);
                    $tokenInfo  = new TokenInfo($data['userId'], 'mock@survey54.com', 'Survey54', $permission, $_SERVER['TOKEN_KEY'], 1800, 'Web');
                    $jwt        = TokenBuilder::generate($tokenInfo);
                    echo "\n\n$jwt\n\n";
                    break;
                case 'generate-colgate-pilot':
                    echo "\n>> Generating Colgate Pilot";
                    $this->container[GenerateColgatePilot::class]->execute();
                    break;
                case 'generate-sample-responses':
                    echo "\n>> Generating Sample Responses";
                    $this->container[GenerateSampleResponses::class]->execute();
                    break;
                case 'generate-am-responses':
                    echo "\n>> Generating Audience Measurement Responses";
                    $this->container[GenerateAMResponses::class]->execute();
                    break;
                case 'generate-all-questions':
                    echo "\n>> Generating All Questions Flows";
                    $this->container[GenerateAllQuestionFlows::class]->execute();
                    break;
                case 'send-airtime':
                    echo "\n>> Sending airtime top up";
                    $this->container[SurveyService::class]->sendAirtimeForSurvey($data['surveyId'], $data['limit']);
                    break;
                case 'send-airtime-csv-numbers':
                    echo "\n>> Sending airtime to numbers";
                    $this->container[SurveyService::class]->sendCsvAirtime($data['incentive'], $data['country'], $data['numbers']);
                    break;
                default:
                    echo "-- Unsupported task.\n";
            }
        } catch (Exception $e) {
            echo "\n-- Error: {$e->getMessage()}\n";
            if ($e instanceof ExtendedException) {
                print_r($e->getData());
            }
        }

        $finish = microtime(true);
        $etaSec = round($finish - $start, 2);
        $etaMin = round($etaSec / 60, 2);
        echo "\n\nCompleted (in sec): $etaSec\n\t  (in min): $etaMin\n\n";
    }
}
