<?php
/**
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Controller questionedit
 * Contains methods to control and view the vuejs based question editor
 *
 * @package   LimeSurvey
 * @author    LimeSurvey Team <support@limesurvey.org>
 * @copyright 2019 LimeSurvey GmbH
 * @access    public
 */

class questionedit extends Survey_Common_Action
{
    /**
     * Main view function prepares the necessary global js parts and renders the HTML
     *
     * @param integer $surveyid
     * @param integer $gid
     * @param integer $qid
     * @param string $landOnSideMenuTab
     */
    public function view($surveyid, $gid = null, $qid = null, $landOnSideMenuTab = '')
    {
        $aData = array();
        $iSurveyID = (int) $surveyid;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $gid = $gid ?? $oSurvey->groups[0]->gid;
        $oQuestion = $this->getQuestionObject($qid, null, $gid);
        Yii::app()->getClientScript()->registerPackage('questioneditor');
        Yii::app()->getClientScript()->registerPackage('ace');
        $qrrow = $oQuestion->attributes;
        $baselang = $oSurvey->language;

        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['questionbar']['buttons']['view'] = true;

        // Last question visited : By user (only one by user)
        $setting_entry = 'last_question_' . Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);

        // we need to set the sid for this question
        $setting_entry = 'last_question_sid_' . Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $iSurveyID);

        // we need to set the gid for this question
        $setting_entry = 'last_question_gid_' . Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $gid);

        // Last question for this survey (only one by survey, many by user)
        $setting_entry = 'last_question_' . Yii::app()->user->getId() . '_' . $iSurveyID;
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);

        // we need to set the gid for this question
        $setting_entry = 'last_question_' . Yii::app()->user->getId() . '_' . $iSurveyID . '_gid';
        SettingGlobal::setSetting($setting_entry, $gid);

        ///////////
        // combine aData
        $aData['surveyid'] = $iSurveyID;
        $aData['oSurvey'] = $oSurvey;
        $aData['aQuestionTypeList'] = QuestionTheme::findAllQuestionMetaDataForSelector();
        $aData['selectedQuestion'] = QuestionTheme::findQuestionMetaData($oQuestion->type);
        $aData['gid'] = $gid;
        $aData['qid'] = $oQuestion->qid;
        $aData['activated'] = $oSurvey->active;
        $aData['oQuestion'] = $oQuestion;
        $aData['languagelist'] = $oSurvey->allLanguages;
        $aData['qshowstyle'] = '';
        $aData['qrrow'] = $qrrow;
        $aData['baselang'] = $baselang;
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');
        $aData['display']['menu_bars']['qid_action'] = 'editquestion';
        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['action'] = 'editquestion';
        $aData['editing'] = true;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title
        . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['surveyIsActive'] = $oSurvey->active !== 'N';
        $aData['activated'] = $oSurvey->active;
        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'surveyObject' => $oSurvey->attributes,
            'gid' => $gid,
            'qid' => $oQuestion->qid,
            'startType' => $oQuestion->type,
            'baseSQACode' => [
                'answeroptions' => SettingsUser::getUserSettingValue('answeroptionprefix', App()->user->id) ?? 'AO' ,
                'subquestions' => SettingsUser::getUserSettingValue('subquestionprefix', App()->user->id) ?? 'SQ',
            ],
            'startInEditView' => SettingsUser::getUserSettingValue('noViewMode', App()->user->id) == '1',
            'connectorBaseUrl' => $this->getController()->createUrl(
                'admin/questioneditor',
                ['sid' => $iSurveyID, 'gid' => $gid, 'sa' => '']
            ),
            'i10N' => [
                'Create question' => gT('Create question'),
                'General settings' => gT("General settings"),
                'Code' => gT('Code'),
                'Text elements' => gT('Text elements'),
                'Question type' => gT('Question type'),
                'Question' => gT('Question'),
                'Help' => gT('Help'),
                'subquestions' => gT('Subquestions'),
                'answeroptions' => gT('Answer options'),
                'Quick add' => gT('Quick add'),
                'Predefined label sets' => gT('Predefined label sets'),
                'Save as label set' => gT('Save as label set'),
                'More languages' => gT('More languages'),
                'Add subquestion' => gT('Add subquestion'),
                'Reset' => gT('Reset'),
                'Save' => gT('Save'),
                'Some example subquestion' => gT('Some example subquestion'),
                'Delete' => gT('Delete'),
                'Open editor' => gT('Open editor'),
                'Duplicate' => gT('Duplicate'),
                'No preview available' => gT('No preview available'),
                'Editor' => gT('Editor'),
                'Quick edit' => gT('Quick edit'),
                'Cancel' => gT('Cancel'),
                'Replace' => gT('Replace'),
                'Add' => gT('Add'),
                'Select delimiter' => gT('Select delimiter'),
                'Semicolon' => gT('Semicolon'),
                'Comma' => gT('Comma'),
                'Tab' => gT('Tab'),
                'New rows' => gT('New rows'),
                'Scale' => gT('Scale'),
                'Save and Close' => gT('Save and Close'),
                'Script' => gT('Script'),
                '__SCRIPTHELP' => gT("This optional script field will be wrapped,"
                    . " so that the script is correctly executed after the question is on the screen."
                    . " If you do not have the correct permissions, this will be ignored"),
                "noCodeWarning" =>
                gT("Please put in a code. Only letters and numbers are allowed. For example [Question1]"),
                "Question cannot be stored. Please check the subquestion codes for duplicates or empty codes." =>
                gT("Question cannot be stored. Please check the subquestion codes for duplicates or empty codes."),
                "Question cannot be stored. Please check the answer option for duplicates or empty titles." =>
                gT("Question cannot be stored. Please check the answer option for duplicates or empty titles."),
            ],
        ];

        $aData['topBar']['type'] = 'question';

        $aData['topBar']['importquestion'] = true;
        $aData['topBar']['showSaveButton'] = true;
        $aData['topBar']['savebuttonform'] = 'frmeditgroup';
        $aData['topBar']['closebuttonurl'] = '/admin/survey/sa/listquestions/surveyid/' . $iSurveyID; // Close button

        if ($landOnSideMenuTab !== '') {
            $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;
        }
        $this->_renderWrappedTemplate('survey/Question2', 'view', $aData);
    }

    /****
     * *** A lot of getter function regarding functionalities and views.
     * *** All called via ajax
     ****/

    /**
     * Returns all languages in a specific survey as a JSON document
     *
     * @param int $iSurveyId
     *
     * @return void
     */
    public function getPossibleLanguages($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        $aLanguages = Survey::model()->findByPk($iSurveyId)->allLanguages;
        $this->renderJSON($aLanguages);
    }

    /**
     * Action called by the FE editor when a save is triggered.
     *
     * @param int $sid Survey id
     *
     * @return void
     */
    public function saveQuestionData($sid)
    {
        $questionData = App()->request->getPost('questionData', []);
        $iSurveyId = (int) $sid;
        $isNewQuestion = false;

        // Store changes to the actual question data, by either storing it, or updating an old one

        $oQuestion = Question::model()->findByPk($questionData['question']['qid']);
        if ($oQuestion != null) {
            $oQuestion = $this->updateQuestionData($oQuestion, $questionData['question']);
        } else {
            $oQuestion = $this->storeNewQuestionData($questionData['question']);
            $isNewQuestion = true;
        }

        // Apply the changes to general settings, advanced settings and translations
        $setApplied = [];

        $setApplied['generalSettings'] = $this->unparseAndSetGeneralOptions(
            $oQuestion,
            $questionData['generalSettings']
        );

        $setApplied['advancedSettings'] = $this->unparseAndSetAdvancedOptions(
            $oQuestion,
            $questionData['advancedSettings']
        );

        $setApplied['questionI10N'] = $this->applyI10N($oQuestion, $questionData['questionI10N']);

        // save advanced attributes default values for given question type
        if (array_key_exists('save_as_default', $questionData['generalSettings'])
            && $questionData['generalSettings']['save_as_default']['formElementValue'] == 'Y') {
            SettingsUser::setUserSetting(
                'question_default_values_' . $questionData['question']['type'],
                ls_json_encode($questionData['advancedSettings'])
            );
        } elseif (array_key_exists('clear_default', $questionData['generalSettings'])
            && $questionData['generalSettings']['clear_default']['formElementValue'] == 'Y') {
            SettingsUser::deleteUserSetting('question_default_values_' . $questionData['question']['type'], '');
        }

        // If set, store subquestions
        if (isset($questionData['scaledSubquestions'])) {
            $setApplied['scaledSubquestions'] = $this->storeSubquestions(
                $oQuestion,
                $questionData['scaledSubquestions']
            );
        }

        // If set, store answer options
        if (isset($questionData['scaledAnswerOptions'])) {
            $setApplied['scaledAnswerOptions'] = $this->storeAnswerOptions(
                $oQuestion,
                $questionData['scaledAnswerOptions']
            );
        }

        // Compile the newly stored data to update the FE
        $oNewQuestion = Question::model()->findByPk($oQuestion->qid);
        $aCompiledQuestionData = $this->getCompiledQuestionData($oNewQuestion);
        $aQuestionAttributeData = $this->getQuestionAttributeData($oQuestion->qid, true);
        $aQuestionGeneralOptions = $this->getGeneralOptions(
            $oQuestion->qid,
            null,
            $oQuestion->gid,
            true,
            $aQuestionAttributeData['question_template']
        );
        $aAdvancedOptions = $this->getAdvancedOptions($oQuestion->qid, null, true);

        // Return a JSON document with the newly stored question data
        $this->renderJSON(
            [
                'success' => array_reduce(
                    $setApplied,
                    function ($coll, $it) {
                        return $coll && $it;
                    },
                    true
                ),
                'message' => gT('Question successfully stored'),
                'successDetail' => $setApplied,
                'questionId' => $oQuestion->qid,
                'redirect' => $this->getController()->createUrl(
                    'admin/questioneditor/sa/view/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $oQuestion->gid,
                        'qid' => $oQuestion->qid,
                    ]
                ),
                'newQuestionDetails' => [
                    "question" => $aCompiledQuestionData['question'],
                    "scaledSubquestions" => $aCompiledQuestionData['subquestions'],
                    "scaledAnswerOptions" => $aCompiledQuestionData['answerOptions'],
                    "questionI10N" => $aCompiledQuestionData['i10n'],
                    "questionAttributes" => $aQuestionAttributeData,
                    "generalSettings" => $aQuestionGeneralOptions,
                    "advancedSettings" => $aAdvancedOptions,
                ],
                'transfer' => $questionData,
            ]
        );
        Yii::app()->close();
    }

    /**
     * Update the data set in the FE
     *
     * @param int $iQuestionId
     * @param string $type
     * @param int $gid Group id
     * @param string $question_template
     *
     * @return void
     */
    public function reloadQuestionData($iQuestionId = null, $type = null, $gid = null, $question_template = 'core')
    {
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId, $type, $gid);

        $aCompiledQuestionData = $this->getCompiledQuestionData($oQuestion);
        $aQuestionGeneralOptions = $this->getGeneralOptions(
            $oQuestion->qid,
            $type,
            $oQuestion->gid,
            true,
            $question_template
        );
        $aAdvancedOptions = $this->getAdvancedOptions($oQuestion->qid, $type, true, $question_template);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
        $aSurveyLanguages = $oQuestion->survey->getAllLanguages();

        array_walk(
            $aSurveyLanguages,
            function ($lngString) use (&$aLanguages, $aAllLanguages) {
                $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
            }
        );

        $this->renderJSON(
            array_merge(
                $aCompiledQuestionData,
                [
                    'languages' => $aLanguages,
                    'mainLanguage' => $oQuestion->survey->language,
                    'generalSettings' => $aQuestionGeneralOptions,
                    'advancedSettings' => $aAdvancedOptions,
                    'questiongroup' => $oQuestion->group->attributes,
                ]
            )
        );
    }

    /**
     * Collect initial question data
     * This either creates a temporary question object, or calls a question object from the database
     *
     * @param int $iQuestionId
     * @param int $gid
     * @param string $type
     *
     * @return void
     */
    public function getQuestionData($iQuestionId = null, $gid = null, $type = null)
    {
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId, $type, $gid);

        $aQuestionInformationObject = $this->getCompiledQuestionData($oQuestion);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
        $aSurveyLanguages = $oQuestion->survey->getAllLanguages();
        array_walk(
            $aSurveyLanguages,
            function ($lngString) use (&$aLanguages, $aAllLanguages) {
                $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
            }
        );

        $this->renderJSON(
            array_merge(
                $aQuestionInformationObject,
                [
                    'languages' => $aLanguages,
                    'mainLanguage' => $oQuestion->survey->language,
                ]
            )
        );
    }

    /**
     * Collect the permissions available for a specific question
     *
     * @param $iQuestionId
     *
     * @return void
     */
    public function getQuestionPermissions($iQuestionId = null)
    {
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId);

        $aPermissions = [
            "read" => Permission::model()->hasSurveyPermission($oQuestion->sid, 'survey', 'read'),
            "update" => Permission::model()->hasSurveyPermission($oQuestion->sid, 'survey', 'update'),
            "editorpreset" => Yii::app()->session['htmleditormode'],
            "script" =>
            SettingsUser::getUserSetting('showScriptEdit', App()->user->id)
            && Permission::model()->hasSurveyPermission($oQuestion->sid, 'survey', 'update'),
        ];

        $this->renderJSON($aPermissions);
    }

    /**
     * Either renders a JSON document of the question attribute array, or returns it
     *
     * @param int $iQuestionId
     * @param boolean $returnArray | If true returns array
     *
     * @return void|array
     */
    protected function getQuestionAttributeData($iQuestionId = null, $returnArray = false)
    {
        $iQuestionId = (int) $iQuestionId;
        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($iQuestionId);
        if ($returnArray === true) {
            return $aQuestionAttributes;
        }
        $this->renderJSON($aQuestionAttributes);
    }

    /**
     * Returns a json document containing the question types
     *
     * @return void
     */
    public function getQuestionTypeList()
    {
        $this->renderJSON(QuestionType::modelsAttributes());
    }

    /**
     * @param string $sQuestionType
     * @return void
     */
    public function getQuestionTypeInformation($sQuestionType)
    {
        $aTypeInformations = QuestionType::modelsAttributes();
        $aQuestionTypeInformation = $aTypeInformations[$sQuestionType];

        $this->renderJSON($aQuestionTypeInformation);
    }

    /**
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @param boolean $returnArray
     * @param string $question_template
     * @return void|array
     */
    public function getGeneralOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $gid = null,
        $returnArray = false,
        $question_template = 'core'
    ) {
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType, $gid);
        $aGeneralOptionsArray = $oQuestion
            ->getDataSetObject()
            ->getGeneralSettingsArray($oQuestion->qid, $sQuestionType, null, $question_template);

        if ($returnArray === true) {
            return $aGeneralOptionsArray;
        }

        $this->renderJSON($aGeneralOptionsArray);
    }

    /**
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param boolean $returnArray
     * @param string $question_template
     * @return void|array
     */
    public function getAdvancedOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $returnArray = false,
        $question_template = 'core'
    ) {
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType);
        $aAdvancedOptionsArray = $oQuestion->getDataSetObject()
            ->getAdvancedOptions($oQuestion->qid, $sQuestionType, null, $question_template);
        if ($returnArray === true) {
            return $aAdvancedOptionsArray;
        }

        $this->renderJSON(
            [
                'advancedSettings' => $aAdvancedOptionsArray,
                'questionTypeDefinition' => $oQuestion->questionType,
            ]
        );
    }

    /**
     * Live preview rendering
     *
     * @param int $iQuestionId
     * @param string $sLanguage
     * @param boolean $root
     *
     * @return void
     */
    public function getRenderedPreview($iQuestionId, $sLanguage, $root = false)
    {
        if ($iQuestionId == null) {
            echo "<h3>No Preview available</h3>";
            return;
        }
        $root = (bool) $root;

        $changedText = App()->request->getPost('changedText', []);
        $changedType = App()->request->getPost('changedType', null);
        $oQuestion = Question::model()->findByPk($iQuestionId);

        $changedType = $changedType == null ? $oQuestion->type : $changedType;

        if ($changedText !== []) {
            Yii::app()->session['edit_' . $iQuestionId . '_changedText'] = $changedText;
        } else {
            $changedText = isset(Yii::app()->session['edit_' . $iQuestionId . '_changedText'])
            ? Yii::app()->session['edit_' . $iQuestionId . '_changedText']
            : [];
        }

        $aFieldArray = [
            //  0 => string qid
            $oQuestion->qid,
            //  1 => string sgqa | This should be working because it is only about parent questions here!
            "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}",
            //  2 => string questioncode
            $oQuestion->title,
            //  3 => string question | technically never used in the new renderers and totally unessecary therefor empty
            "",
            //  4 => string type
            $oQuestion->type,
            //  5 => string gid
            $oQuestion->gid,
            //  6 => string mandatory,
            ($oQuestion->mandatory == 'Y'),
        ];
        Yii::import('application.helpers.qanda_helper', true);
        setNoAnswerMode(['shownoanswer' => $oQuestion->survey->shownoanswer]);

        if (!isset($_SESSION["survey_{$oQuestion->sid}"])) {
            buildsurveysession($oQuestion->sid, true);
        }

        $oQuestionRenderer = $oQuestion->getRenderererObject($aFieldArray, $changedType);
        $aRendered = $oQuestionRenderer->render();
        $aSurveyInfo = $oQuestion->survey->attributes;
        $aQuestion = array_merge(
            $oQuestion->attributes,
            QuestionAttribute::model()->getQuestionAttributes($iQuestionId),
            ['answer' => $aRendered[0]],
            [
                'number' => $oQuestion->question_order,
                'code' => $oQuestion->title,
                'text' => isset($changedText['question'])
                ? $changedText['question']
                : $oQuestion->questionL10ns[$sLanguage]->question,
                'help' => [
                    'show' => true,
                    'text' => (isset($changedText['help'])
                        ? $changedText['help']
                        : $oQuestion->questionL10ns[$sLanguage]->help),
                ],
            ]
        );

        // If the template instance is not reset, it will load the last used one.
        // This may be correct, but oftentimes it is not and to not leave it for luck and chance => Reset
        Template::resetInstance();
        Template::getInstance($oQuestion->survey->template);
        Yii::app()->twigRenderer->renderTemplateForQuestionEditPreview(
            '/subviews/survey/question_container.twig',
            ['aSurveyInfo' => $aSurveyInfo, 'aQuestion' => $aQuestion, 'session' => $_SESSION],
            $root
        );
    }

    /**
     * Renders the topbar definition JSON document
     *
     * @param int $qid
     * @return void
     */
    public function getQuestionTopbar($qid = null)
    {
        $oQuestion = $this->getQuestionObject($qid);
        $qtypes = Question::typeList();
        $qrrow = $oQuestion->attributes;
        $ownsSaveButton = true;
        $ownsSaveAndCloseButton = true;
        $ownsCloseButton = true;

        return Yii::app()->getController()->renderPartial(
            '/admin/survey/topbar/question_topbar',
            array(
                'oSurvey' => $oQuestion->survey,
                'sid' => $oQuestion->sid,
                'gid' => $oQuestion->gid,
                'qid' => $oQuestion->qid,
                'qrrow' => $qrrow,
                'qtypes' => $qtypes,
                'ownsSaveButton' => $ownsSaveButton,
                'ownsSaveAndCloseButton' => $ownsSaveAndCloseButton,
                'ownsCloseButton' => $ownsCloseButton,
            ),
            false,
            false
        );
    }

    /**
     * Creates a question object
     * This is either an instance of the placeholder model QuestionCreate for new questions,
     * or of Question for already existing ones
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @return Question
     */
    private function getQuestionObject($iQuestionId = null, $sQuestionType = null, $gid = null)
    {
        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        $oQuestion = Question::model()->findByPk($iQuestionId);

        if ($oQuestion == null) {
            $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType);
        }

        if ($sQuestionType != null) {
            $oQuestion->type = $sQuestionType;
        }

        if ($gid != null) {
            $oQuestion->gid = $gid;
        }

        return $oQuestion;
    }

    /**
     * Method to store and filter questionData for a new question
     *
     * @param array $aQuestionData
     * @param boolean $subquestion
     * @return Question
     * @throws CHttpException
     */
    private function storeNewQuestionData($aQuestionData = null, $subquestion = false)
    {
        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $iQuestionGroupId = Yii::app()->request->getParam('gid');
        $type = SettingsUser::getUserSettingValue(
            'preselectquestiontype',
            null,
            null,
            null,
            Yii::app()->getConfig('preselectquestiontype')
        );

        $aQuestionData = array_merge([
            'sid' => $iSurveyId,
            'gid' => Yii::app()->request->getParam('gid'),
            'type' => $type,
            'other' => 'N',
            'mandatory' => 'N',
            'relevance' => 1,
            'group_name' => '',
            'modulename' => '',
        ], $aQuestionData);
        unset($aQuestionData['qid']);

        if ($subquestion) {
            foreach ($oSurvey->allLanguages as $sLanguage) {
                unset($aQuestionData[$sLanguage]);
            }
        } else {
            $aQuestionData['question_order'] = getMaxQuestionOrder($iQuestionGroupId);
        }

        $oQuestion = new Question();
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new CHttpException(500, "Object creation failed, input array malformed or invalid");
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw new CHttpException(
                500,
                "Object creation failed, couldn't save.\n ERRORS:"
                . print_r($oQuestion->getErrors(), true)
            );
        }

        $i10N = [];
        foreach ($oSurvey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = new QuestionL10n();
            $i10N[$sLanguage]->setAttributes([
                'qid' => $oQuestion->qid,
                'language' => $sLanguage,
                'question' => '',
                'help' => '',
            ], false);
            $i10N[$sLanguage]->save();
        }

        return $oQuestion;
    }

    /**
     * Method to store and filter questionData for editing a question
     *
     * @param Question $oQuestion
     * @param array $aQuestionData
     * @return Question
     * @throws CHttpException
     */
    private function updateQuestionData(&$oQuestion, $aQuestionData)
    {
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new CHttpException(500, "Object update failed, input array malformed or invalid");
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw new CHttpException(
                500,
                "Object update failed, couldn't save. ERRORS:"
                . print_r($oQuestion->getErrors(), true)
            );
        }
        return $oQuestion;
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     */
    private function unparseAndSetGeneralOptions(&$oQuestion, $dataSet)
    {
        $aQuestionBaseAttributes = $oQuestion->attributes;

        foreach ($dataSet as $sAttributeKey => $aAttributeValueArray) {
            if ($sAttributeKey === 'debug' || !isset($aAttributeValueArray['formElementValue'])) {
                continue;
            }
            if (array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                $oQuestion->$sAttributeKey = $aAttributeValueArray['formElementValue'];
            } else {
                if (!QuestionAttribute::model()->setQuestionAttribute(
                    $oQuestion->qid,
                    $sAttributeKey,
                    $aAttributeValueArray['formElementValue']
                )) {
                    throw new CHttpException(500, gT("Could not store general options"));
                }
            }
        }

        if (!$oQuestion->save()) {
            throw new CHttpException(500, gT("Could not store general options"));
        }

        return true;
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     */
    private function unparseAndSetAdvancedOptions(&$oQuestion, $dataSet)
    {
        $aQuestionBaseAttributes = $oQuestion->attributes;

        foreach ($dataSet as $sAttributeCategory => $aAttributeCategorySettings) {
            if ($sAttributeCategory === 'debug') {
                continue;
            }
            foreach ($aAttributeCategorySettings as $sAttributeKey => $aAttributeValueArray) {
                if (!isset($aAttributeValueArray['formElementValue'])) {
                    continue;
                }
                $newValue = $aAttributeValueArray['formElementValue'];

                // Set default value if empty.
                if ($newValue === ""
                    && isset($aAttributeValueArray['aFormElementOptions']['default'])) {
                    $newValue = $aAttributeValueArray['aFormElementOptions']['default'];
                }

                if (is_array($newValue)) {
                    foreach ($newValue as $lngKey => $content) {
                        if ($lngKey == 'expression') {
                            continue;
                        }
                        if (!QuestionAttribute::model()->setQuestionAttributeWithLanguage(
                            $oQuestion->qid,
                            $sAttributeKey,
                            $content,
                            $lngKey
                        )) {
                            throw new CHttpException(500, gT("Could not store advanced options"));
                        }
                    }
                } else {
                    if (array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                        $oQuestion->$sAttributeKey = $newValue;
                    } else {
                        if (!QuestionAttribute::model()->setQuestionAttribute(
                            $oQuestion->qid,
                            $sAttributeKey,
                            $newValue
                        )) {
                            throw new CHttpException(500, gT("Could not store advanced options"));
                        }
                    }
                }
            }
        }

        if (!$oQuestion->save()) {
            throw new CHttpException(500, gT("Could not store advanced options"));
        }

        return true;
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     */
    private function applyI10N(&$oQuestion, $dataSet)
    {

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = QuestionL10n::model()->findByAttributes(['qid' => $oQuestion->qid, 'language' => $sLanguage]);
            $i10N->setAttributes([
                'question' => $aI10NBlock['question'],
                'help' => $aI10NBlock['help'],
                'script' => $aI10NBlock['script'],
            ], false);
            if (!$i10N->save()) {
                throw new CHttpException(500, gT("Could not store translation"));
            }
        }

        return true;
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     */
    private function applyI10NSubquestion($oQuestion, $dataSet)
    {

        foreach ($oQuestion->survey->allLanguages as $sLanguage) {
            $aI10NBlock = $dataSet[$sLanguage];
            $i10N = QuestionL10n::model()->findByAttributes(['qid' => $oQuestion->qid, 'language' => $sLanguage]);
            $i10N->setAttributes([
                'question' => $aI10NBlock['question'],
                'help' => $aI10NBlock['help'],
            ], false);
            if (!$i10N->save()) {
                throw new CHttpException(500, gT("Could not store translation for subquestion"));
            }
        }

        return true;
    }

    /**
     * @param Answer $oAnswer
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     */
    private function applyAnswerI10N($oAnswer, $oQuestion, $dataSet)
    {
        foreach ($oQuestion->survey->allLanguages as $sLanguage) {
            $i10N = AnswerL10n::model()->findByAttributes(['aid' => $oAnswer->aid, 'language' => $sLanguage]);
            if ($i10N == null) {
                $i10N = new AnswerL10n();
                $i10N->setAttributes([
                    'aid' => $oAnswer->aid,
                    'language' => $sLanguage,
                ], false);
            }
            $i10N->setAttributes([
                'answer' => $dataSet[$sLanguage]['answer'],
            ], false);

            if (!$i10N->save()) {
                throw new CHttpException(500, gT("Could not store translation for answer option"));
            }
        }

        return true;
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return void
     * @todo PHPDoc description
     */
    private function cleanSubquestions(&$oQuestion, &$dataSet)
    {
        $aSubquestions = $oQuestion->subquestions;
        array_walk(
            $aSubquestions,
            function ($oSubquestion) use (&$dataSet) {
                $exists = false;
                foreach ($dataSet as $scaleId => $aSubquestions) {
                    foreach ($aSubquestions as $i => $aSubquestionDataSet) {
                        if (((is_numeric($aSubquestionDataSet['qid'])
                            && $oSubquestion->qid == $aSubquestionDataSet['qid'])
                            || $oSubquestion->title == $aSubquestionDataSet['title'])
                            && ($oSubquestion->scale_id == $scaleId)
                        ) {
                            $exists = true;
                            $dataSet[$scaleId][$i]['qid'] = $oSubquestion->qid;
                        }

                        if (!$exists) {
                            $oSubquestion->delete();
                        }
                    }
                }
            }
        );
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     */
    private function storeSubquestions(&$oQuestion, $dataSet)
    {
        $this->cleanSubquestions($oQuestion, $dataSet);
        foreach ($dataSet as $aSubquestions) {
            foreach ($aSubquestions as $aSubquestionDataSet) {
                $oSubQuestion = Question::model()->findByPk($aSubquestionDataSet['qid']);
                if ($oSubQuestion != null) {
                    $oSubQuestion = $this->updateQuestionData($oSubQuestion, $aSubquestionDataSet);
                } else {
                    $aSubquestionDataSet['parent_qid'] = $oQuestion->qid;
                    $oSubQuestion = $this->storeNewQuestionData($aSubquestionDataSet, true);
                }
                $this->applyI10NSubquestion($oSubQuestion, $aSubquestionDataSet);
            }
        }

        return true;
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return void
     */
    private function cleanAnsweroptions(&$oQuestion, &$dataSet)
    {
        $aAnsweroptions = $oQuestion->answers;
        array_walk(
            $aAnsweroptions,
            function ($oAnsweroption) use (&$dataSet) {
                $exists = false;
                foreach ($dataSet as $scaleId => $aAnsweroptions) {
                    foreach ($aAnsweroptions as $i => $aAnsweroptionDataSet) {
                        if (((is_numeric($aAnsweroptionDataSet['aid'])
                            && $oAnsweroption->aid == $aAnsweroptionDataSet['aid'])
                            || $oAnsweroption->code == $aAnsweroptionDataSet['code'])
                            && ($oAnsweroption->scale_id == $scaleId)
                        ) {
                            $exists = true;
                            $dataSet[$scaleId][$i]['aid'] = $oAnsweroption->aid;
                        }

                        if (!$exists) {
                            $oAnsweroption->delete();
                        }
                    }
                }
            }
        );
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     */
    private function storeAnswerOptions(&$oQuestion, $dataSet)
    {
        $this->cleanAnsweroptions($oQuestion, $dataSet);
        foreach ($dataSet as $aAnswerOptions) {
            foreach ($aAnswerOptions as $iScaleId => $aAnswerOptionDataSet) {
                $aAnswerOptionDataSet['sortorder'] = (int) $aAnswerOptionDataSet['sortorder'];
                $oAnswer = Answer::model()->findByPk($aAnswerOptionDataSet['aid']);
                if ($oAnswer == null) {
                    $oAnswer = new Answer();
                    $oAnswer->qid = $oQuestion->qid;
                    unset($aAnswerOptionDataSet['aid']);
                    unset($aAnswerOptionDataSet['qid']);
                }
                if (empty($aAnswerOptionDataSet['code'])) {
                    throw new CHttpException(
                        500,
                        "Answer option code cannot be empty"
                    );
                }
                $oAnswer->setAttributes($aAnswerOptionDataSet);
                $answerSaved = $oAnswer->save();
                if (!$answerSaved) {
                    throw new CHttpException(
                        "Answer option couldn't be saved. Error: "
                        . print_r($oAnswer->getErrors(), true)
                    );
                }
                $this->applyAnswerI10N($oAnswer, $oQuestion, $aAnswerOptionDataSet);
            }
        }
        return true;
    }

    /**
     * @param Question $oQuestion
     * @return array
     */
    private function getCompiledQuestionData(&$oQuestion)
    {
        LimeExpressionManager::StartProcessingPage(false, true);
        $aQuestionDefinition = array_merge($oQuestion->attributes, ['typeInformation' => $oQuestion->questionType]);
        $oQuestionGroup = QuestionGroup::model()->findByPk($oQuestion->gid);
        $aQuestionGroupDefinition = array_merge($oQuestionGroup->attributes, $oQuestionGroup->questionGroupL10ns);

        $aScaledSubquestions = $oQuestion->getOrderedSubQuestions();
        foreach ($aScaledSubquestions as $scaleId => $aSubquestions) {
            $aScaledSubquestions[$scaleId] = array_map(function ($oSubQuestion) {
                return array_merge($oSubQuestion->attributes, $oSubQuestion->questionL10ns);
            }, $aSubquestions);
        }

        $aScaledAnswerOptions = $oQuestion->getOrderedAnswers();
        foreach ($aScaledAnswerOptions as $scaleId => $aAnswerOptions) {
            $aScaledAnswerOptions[$scaleId] = array_map(function ($oAnswerOption) {
                return array_merge($oAnswerOption->attributes, $oAnswerOption->answerL10ns);
            }, $aAnswerOptions);
        }
        $aReplacementData = [];
        $questioni10N = [];
        foreach ($oQuestion->questionL10ns as $lng => $oQuestionI10N) {
            $questioni10N[$lng] = $oQuestionI10N->attributes;

            templatereplace(
                $oQuestionI10N->question,
                array(),
                $aReplacementData,
                'Unspecified',
                false,
                $oQuestion->qid
            );

            $questioni10N[$lng]['question_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );

            templatereplace($oQuestionI10N->help, array(), $aReplacementData, 'Unspecified', false, $oQuestion->qid);
            $questioni10N[$lng]['help_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );
        }
        LimeExpressionManager::FinishProcessingPage();
        return [
            'question' => $aQuestionDefinition,
            'questiongroup' => $aQuestionGroupDefinition,
            'i10n' => $questioni10N,
            'subquestions' => $aScaledSubquestions,
            'answerOptions' => $aScaledAnswerOptions,
        ];
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate(
        $sAction = 'survey/Question2',
        $aViewUrls = array(),
        $aData = array(),
        $sRenderFile = false
    ) {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}