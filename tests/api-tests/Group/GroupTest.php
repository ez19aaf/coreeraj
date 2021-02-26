<?php


namespace Tests\ApiTest\Group;


use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\GroupType;
use Survey54\Library\Domain\Values\Recurrence;
use Tests\ApiTest\AbstractTestCase;

class GroupTest extends AbstractTestCase
{
    public function testPostGroup(): void
    {
        $action = self::$groupData['groupType'];
        $options = [];
        switch ($action) {
            case GroupType::DEMOGRAPHIC:
                $options['json'] = [
                    'userId'        => $this->userId,
                    'groupName'     => self::$groupData['groupName'],
                    'country'       => self::$groupData['country'],
                    'sample'        => self::$groupData['sample'],
                    'quantity'      => self::$groupData['quantity'],
                    'groupType'     => GroupType::DEMOGRAPHIC,
                    'recurrence'    => Recurrence::MONTHLY
                ];
                break;
            case GroupType::UPLOADED:
                $options['json'] = [
                    'userId' => $this->userId,
                    'groupName'  => 'test-group',
                    'audience'  => [
                        [
                            'firstName'   => 'Samuels',
                            'lastName'    => 'xyz',
                            'mobile'      => '447878422345',
                            "region"      => 'Londons',
                        ],
                        [
                            'firstName' => 'Samuel',
                            'lastName' => 'xyz',
                            'mobile' => '4487993280815',
                            'region' => 'Africa',
                        ],
                    ],
                    'groupType'     => GroupType::UPLOADED,
                    'recurrence'    => Recurrence::MONTHLY
                ];
                break;
             }
        $response = $this->app->post('/group', $options);

        self::assertEquals(202, $response->getStatusCode());
    }

    public function testUpdateGroup(): void
    {
        $options['json'] = [
            'uuid'          => self::$groupData['uuid'],
            'userId'        => $this->userId,
            'groupName'     => self::$groupData['groupName'],
            'country'       => self::$groupData['country'],
            'sample'        => self::$groupData['sample'],
            'quantity'      => self::$groupData['quantity'],
            'groupType'     => GroupType::DEMOGRAPHIC,
            'recurrence'    => Recurrence::MONTHLY
        ];

        $response = $this->app->put('/group/' . self::$groupData['uuid'], $options);

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteGroup(): void
    {
        $options['json'] = ['xx.xx.xxx'];

        $response = $this->app->delete('/group/delete', $options);

        self::assertEquals(202, $response->getStatusCode());
    }
}