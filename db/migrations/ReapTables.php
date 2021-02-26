<?php

use Phinx\Db\Table;
use Survey54\Library\Domain\Values\AgeGroup;
use Survey54\Library\Domain\Values\AuthStatus;
use Survey54\Library\Domain\Values\Country;
use Survey54\Library\Domain\Values\Employment;
use Survey54\Library\Domain\Values\Gender;
use Survey54\Library\Domain\Values\IntegrationType;
use Survey54\Library\Domain\Values\LsmGroup;
use Survey54\Library\Domain\Values\Race;
use Survey54\Library\Domain\Values\RespondentSurveyStatus;
use Survey54\Library\Domain\Values\SignedUpSource;
use Survey54\Library\Domain\Values\UserStatus;
use Survey54\Library\Domain\Values\VerificationType;

class ReapTables
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
        $airtimeLogs = $this->start('airtime_logs')
            ->addColumn('mobile', 'string', ['limit' => 50])
            ->addColumn('redeemed', 'boolean', ['default' => false])
            ->addColumn('proof', 'json', ['null' => true])
            ->addColumn('errored', 'boolean', ['default' => false])
            ->addColumn('error', 'text', ['null' => true]);
        $this->finish($airtimeLogs);

        $appReview = $this->start('app_review')
            ->addColumn('dontShow', 'boolean', ['default' => false]);
        $this->finish($appReview); // uuid = respondentId

        $ghost = $this->start('ghost')
            ->addColumn('mobile', 'string', ['limit' => 50]) // unique
            ->addColumn('ghostMobile', 'string', ['limit' => 50])
            ->addColumn('organisationId', 'string', ['limit' => 36]);
        $this->finish($ghost);

        $insight = $this->start('insight')
            ->addColumn('userId', 'string', ['limit' => 36])
            ->addColumn('surveyId', 'string', ['limit' => 36])
            ->addColumn('summary', 'text')
            ->addForeignKey('surveyId', 'survey', 'uuid');
        $this->finish($insight);

        $logs = $this->start('log')
            ->addColumn('objectId', 'string', ['limit' => 36])
            ->addColumn('objectType', 'string', ['limit' => 50])
            ->addColumn('action', 'string', ['limit' => 30])
            ->addColumn('request', 'json', ['null' => true])
            ->addColumn('response', 'json', ['null' => true]);
        $this->finish($logs);

        $respondent = $this->start('respondent')
            ->addColumn('firstName', 'string', ['null' => true, 'limit' => 20])
            ->addColumn('lastName', 'string', ['null' => true, 'limit' => 20])
            ->addColumn('email', 'string', ['null' => true, 'limit' => 200])
            ->addColumn('mobile', 'string', ['limit' => 50])
            ->addColumn('dateOfBirth', 'string', ['null' => true])
            ->addColumn('ageGroup', 'enum', ['null' => true, 'values' => AgeGroup::toArray()])
            ->addColumn('employment', 'enum', ['null' => true, 'values' => Employment::toArray()])
            ->addColumn('gender', 'enum', ['null' => true, 'values' => Gender::toArray()])
            ->addColumn('race', 'enum', ['null' => true, 'values' => Race::toArray()])
            ->addColumn('lsm', 'json', ['null' => true])
            ->addColumn('lsmGroup', 'enum', ['null' => true, 'values' => LsmGroup::toArray()])
            ->addColumn('country', 'string', ['null' => true, 'limit' => 200])
            ->addColumn('region', 'string', ['null' => true, 'limit' => 200])
            ->addColumn('ipAddress', 'string', ['null' => true])
            ->addColumn('profileImage', 'json', ['null' => true]) // new
            ->addColumn('userStatus', 'enum', ['values' => UserStatus::toArray()])
            ->addColumn('authStatus', 'enum', ['values' => AuthStatus::toArray()])
            ->addColumn('password', 'string', ['null' => true, 'limit' => 60])//strlen($pass)=60
            ->addColumn('action', 'string', ['null' => true])
            ->addColumn('verificationCode', 'text', ['null' => true, 'limit' => 160])
            ->addColumn('verificationType', 'enum', ['null' => true, 'values' => VerificationType::toArray()])
            ->addColumn('verificationExpiry', 'string', ['null' => true, 'limit' => 25])
            ->addColumn('verificationRetries', 'integer', ['default' => 0])
            ->addColumn('refreshToken', 'string', ['null' => true])
            ->addColumn('refreshTokenExpiry', 'string', ['null' => true, 'limit' => 25])
            ->addColumn('loginAttempts', 'integer', ['default' => 0])
            ->addColumn('demographicCompleted', 'boolean', ['default' => false])
            ->addColumn('signedUpSource', 'enum', ['null' => true, 'default' => SignedUpSource::WEB, 'values' => SignedUpSource::toArray()])
            ->addColumn('convertedFromOpenSurvey', 'boolean', ['default' => false])
            ->addColumn('markedForDeletion', 'boolean', ['default' => false])
            ->addColumn('isSample', 'boolean', ['default' => false])
            ->addColumn('isGhost', 'boolean', ['default' => false])
            ->addColumn('ghostMobile', 'string', ['null' => true, 'limit' => 50])
            ->addColumn('promptReview', 'boolean', ['default' => false])
            ->addIndex(['mobile'], ['unique' => true])
            ->addIndex(['email'], ['unique' => true]);
        $this->finish($respondent);

        $respondentSurvey = $this->start('respondent_survey')
            ->addColumn('respondentId', 'string', ['limit' => 50])
            ->addColumn('surveyId', 'string', ['limit' => 36])
            ->addColumn('status', 'enum', ['values' => RespondentSurveyStatus::toArray()])
            ->addColumn('ipAddress', 'string', ['null' => true, 'limit' => 50])
            ->addColumn('redeemed', 'boolean', ['default' => false])
            ->addColumn('proof', 'json', ['null' => true])
            ->addColumn('errored', 'boolean', ['default' => false])
            ->addColumn('error', 'text', ['null' => true])
            ->addColumn('nextQuestionId', 'integer', ['null' => true])
            ->addColumn('gotoMap', 'json', ['null' => true])
            ->addColumn('entryLink', 'string', ['null' => true])
            ->addColumn('integrationType', 'enum', ['null' => true, 'values' => IntegrationType::toArray()])
            ->addForeignKey('respondentId', 'respondent', 'uuid')
            ->addForeignKey('surveyId', 'survey', 'uuid')//remove
            ->addIndex(['respondentId', 'surveyId'], ['unique' => true]);
        $this->finish($respondentSurvey);

        $response = $this->start('response')
            ->addColumn('respondentId', 'string', ['limit' => 50])
            ->addColumn('surveyId', 'string', ['limit' => 36])
            ->addColumn('questionId', 'integer')
            ->addColumn('goto', 'integer', ['null' => true])
            ->addColumn('answer', 'string', ['null' => true, 'limit' => 200])
            ->addColumn('answerIds', 'json', ['null' => true])
            ->addColumn('answerRank', 'integer', ['null' => true])
            ->addColumn('answerScale', 'integer', ['null' => true])
            ->addColumn('ageGroup', 'enum', ['null' => true, 'values' => AgeGroup::toArray()])
            ->addColumn('employment', 'enum', ['null' => true, 'values' => Employment::toArray()])
            ->addColumn('gender', 'enum', ['null' => true, 'values' => Gender::toArray()])
            ->addColumn('race', 'enum', ['null' => true, 'values' => Race::toArray()])
            ->addColumn('lsmGroup', 'enum', ['null' => true, 'values' => LsmGroup::toArray()])
            ->addColumn('boundTime', 'string', ['null' => true, 'limit' => 25])
            ->addColumn('boundDate', 'string', ['null' => true, 'limit' => 25])
            ->addForeignKey('respondentId', 'respondent', 'uuid')
            ->addForeignKey('surveyId', 'survey', 'uuid');
        $this->finish($response);
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
