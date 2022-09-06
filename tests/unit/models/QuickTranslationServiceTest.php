<?php

namespace ls\tests;

use LimeSurvey\Models\Services\QuickTranslation;

class QuickTranslationServiceTest extends TestBaseClass
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $surveyFile = self::$surveysFolder . '/limesurvey_survey_161359_quickTranslation.lss';
        self::importSurvey($surveyFile);
    }

    public function testGetTranslationsNoTranslation()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('title', 'aa');
        $this->assertEmpty($ts);
    }

    public function testGetTranslationsEnglishTitle()
    {
        $qt = new QuickTranslation(self::$testSurvey);
        $ts = $qt->getTranslations('title', 'en');
        $this->assertNotEmpty($ts);
        $this->assertEquals($ts[0]->surveyls_title, 'translation');
    }
}
