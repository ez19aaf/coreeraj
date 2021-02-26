<?php

use Phinx\Db\Table;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\Recurrence;
use Survey54\Library\Domain\Values\GroupType;
use Survey54\Library\Domain\Values\SurveyPushTo;
use Survey54\Library\Domain\Values\SurveyStatus;
use Survey54\Library\Domain\Values\SurveyType;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\UserType;

class CoreTables
{
    private InitialSetup $setup;

    /**
     * @param InitialSetup $setup
     * @return $this
     */
    public function setSetup(InitialSetup $setup): self
    {
        $this->setup = $setup;
        return $this;
    }

    public function create(): void
    {
        $auth = $this->start('auth')
            ->addColumn('type', 'enum', ['values' => UserType::toArray()])
            ->addColumn('status', 'enum', ['values' => AuthStatus::toArray()])
            ->addColumn('password', 'string', ['limit' => 60])//strlen($pass)=60
            ->addColumn('action', 'string', ['null' => true])
            ->addColumn('verificationCode', 'text', ['null' => true, 'limit' => 160])
            ->addColumn('verificationExpiry', 'string', ['null' => true, 'limit' => 25])
            ->addColumn('refreshToken', 'string', ['null' => true])
            ->addColumn('refreshTokenExpiry', 'string', ['null' => true, 'limit' => 25])
            ->addColumn('loginAttempts', 'integer', ['default' => 0]);
        $this->finish($auth);

        $card = $this->start('card')
            ->addColumn('userId', 'string', ['limit' => 36])
            ->addColumn('name', 'string', ['limit' => 20])
            ->addColumn('cardId', 'string', ['limit' => 40])//stripe
            ->addColumn('fingerprint', 'string', ['limit' => 30])//stripe
            ->addColumn('isDefault', 'boolean', ['default' => false]);
        $this->finish($card);

        $gdpr = $this->start('gdpr')
            ->addColumn('userId', 'string', ['limit' => 36])
            ->addColumn('userType', 'enum', ['values' => UserType::toArray()])
            ->addColumn('action', 'string', ['limit' => 30])
            ->addColumn('duration', 'integer', ['null' => true]);
        $this->finish($gdpr);

        $group = $this->start('group')
            ->addColumn('userId', 'string', ['limit' => 36])
            ->addColumn('groupName', 'string', ['limit' => 30])
            ->addColumn('recurrence', 'enum', ['values' => Recurrence::toArray()])
            ->addColumn('audience', 'json', ['null' => true]) //list of mobiles or list of respondentIds
            ->addColumn('startDate', 'string')
            ->addColumn('endDate', 'string');

        $this->finish($group);

        $order = $this->start('order')
            ->addColumn('userId', 'string', ['limit' => 36])
            ->addColumn('surveyId', 'string', ['limit' => 36])
            ->addColumn('amount', 'string', ['null' => true, 'limit' => 10])// use int of lowest currency
            ->addColumn('breakdown', 'text', ['null' => true])
            ->addColumn('balanceTransaction', 'string', ['null' => true, 'limit' => 40])
            ->addColumn('currency', 'string', ['null' => true, 'limit' => 3])
            ->addColumn('status', 'string', ['null' => true, 'limit' => 50]);
        $this->finish($order);

        $survey = $this->start('survey')
            ->addColumn('userId', 'string', ['limit' => 36])
            ->addColumn('title', 'string', ['limit' => 100])
            ->addColumn('description', 'string', ['null' => true, 'limit' => 500])
            ->addColumn('type', 'enum', ['null' => true, 'values' => SurveyType::toArray()])
            ->addColumn('expectedCompletes', 'integer', ['null' => true, 'limit' => 50])
            ->addColumn('countries', 'json', ['null' => true])
            ->addColumn('sample', 'json', ['null' => true])
            ->addColumn('questions', 'json', ['null' => true])
            ->addColumn('image', 'string', ['null' => true])
            ->addColumn('groupId', 'string', ['null' => true, 'limit' => 36])
            ->addColumn('audience', 'json', ['null' => true])
            ->addColumn('tagIds', 'string', ['null' => true])
            ->addColumn('tagLabels', 'json', ['null' => true])
            ->addColumn('favourite', 'boolean', ['default' => false])
            ->addColumn('status', 'enum', ['values' => SurveyStatus::toArray()])
            ->addColumn('orderId', 'string', ['null' => true, 'limit' => 36])
            ->addColumn('countScreeningQuestions', 'integer', ['default' => 0])
            ->addColumn('incidentRate', 'integer', ['null' => true])
            ->addColumn('lengthOfInterview', 'integer', ['null' => true])
            ->addColumn('incentive', 'integer', ['null' => true])
            ->addColumn('incentiveCurrency', 'string', ['null' => true, 'limit' => 3])
            ->addColumn('smsCode', 'string', ['null' => true])
            ->addColumn('ussdCode', 'string', ['null' => true])
            ->addColumn('actualCompletes', 'integer', ['default' => 0])
            ->addColumn('actualCompletesPercent', 'integer', ['default' => 0])
            ->addColumn('category', 'string', ['null' => true, 'limit' => 50]) // e.g. TV
            ->addColumn('subject', 'string', ['null' => true, 'limit' => 100]) // e.g. MTv
            ->addColumn('recurrence', 'json', ['null' => true])
            ->addColumn('pushNotification', 'boolean', ['default' => false])
            ->addColumn('pushTo', 'enum', ['null' => true, 'values' => SurveyPushTo::toArray()]);
        $this->finish($survey);

        $tag = $this->start('tag')
            ->addColumn('userId', 'string', ['limit' => 36])
            ->addColumn('label', 'string', ['limit' => 30])
            ->addColumn('color', 'string', ['limit' => 30])
            ->addColumn('isReserved', 'boolean', ['default' => false]);
        $this->finish($tag);

        $template = $this->start('template')
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('image', 'text')
            ->addColumn('description', 'text')
            ->addColumn('questions', 'text', ['null' => true]);
        $this->finish($template);

        $user = $this->start('user')
            ->addColumn('type', 'enum', ['values' => UserType::toArray()])
            ->addColumn('firstName', 'string', ['limit' => 20])
            ->addColumn('lastName', 'string', ['limit' => 20])
            ->addColumn('mobile', 'string', ['null' => true, 'limit' => 20])// null respondents can't do sms/dialpad surveys
            ->addColumn('email', 'string', ['limit' => 50])
            ->addColumn('city', 'string', ['null' => true, 'limit' => 50])
            ->addColumn('country', 'string', ['null' => true, 'limit' => 50])
            ->addColumn('profileImage', 'json', ['null' => true])
            ->addColumn('status', 'enum', ['values' => UserStatus::toArray()])
            ->addColumn('company', 'string', ['null' => true, 'limit' => 50])
            ->addColumn('stripeCustomerId', 'string', ['null' => true, 'limit' => 36])
            ->addColumn('ageGroup', 'enum', ['null' => true, 'values' => AgeGroup::toArray()])
            ->addColumn('employment', 'enum', ['null' => true, 'values' => Employment::toArray()])
            ->addColumn('gender', 'enum', ['null' => true, 'values' => Gender::toArray()])
            ->addIndex('email', ['unique' => true]);
        $this->finish($user);
    }

    /**
     * @param string $tableName
     * @return Table
     */
    private function start(string $tableName): Table
    {
        return $this->setup->table($tableName, ['id' => false, 'primary_key' => ['uuid']])
            ->addColumn('uuid', 'string', ['limit' => 36])
            ->addIndex(['uuid'], ['unique' => true]);
    }

    /**
     * @param Table $table
     */
    private function finish(Table $table): void
    {
        $table->addColumn('createdAt', 'string', ['limit' => 25])
            ->addColumn('updatedAt', 'string', ['limit' => 25, 'null' => true])
            ->create();
    }
}
