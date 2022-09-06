<?php

namespace ls\tests;

use LimeSurvey\Models\Services\QuickTranslation;

class QuickTranslationServiceTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        error_reporting(E_ALL);

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss';
        self::importSurvey($surveyFile);
    }

    public function testGetTranslations()
    {
        var_dump(self::$surveyId);
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('title');
        var_dump($ts);
    }
}
