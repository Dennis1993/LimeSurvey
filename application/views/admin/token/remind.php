<?php
/**
 * Send email reminder
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Send email reminder"); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo PrepareEditorScript(true, $this); ?>

            <?php if ($thissurvey['active'] != 'Y'): ?>
                <?php if ($thissurvey[$baselang]['active'] != 'Y'): ?>
                    <div class="jumbotron message-box message-box-error">
                        <h2 class='text-warning'><?php eT('Warning!'); ?></h2>
                        <p class="lead text-warning">
                            <?php eT("This survey is not yet activated and so your participants won't be able to fill out the survey."); ?>
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php echo CHtml::form(array("admin/tokens/sa/email/action/remind/surveyid/{$surveyid}"), 'post', array('id' => 'sendreminder', 'class' => '')); ?>
            <div class="row">
                <div class="col-sm-6">
                    <?php if (count($tokenids) > 0): ?>
                        <div class='form-group'>
                            <label class='form-label '><?php eT("Send reminder to participant ID(s):"); ?></label>
                            <div class=''>
                                <?php echo short_implode(", ", "-", (array)$tokenids); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class='form-group'>
                        <label class='form-label '
                               for='bypassbademails'><?php eT("Bypass participants with failing email addresses:"); ?></label>
                        <div class=''>
                            <?php
                            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => "bypassbademails",
                                'id' => "bypassbademails",
                                'value' => '1',
                                'onLabel' => gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                    </div>

                    <div class='form-group'>
                        <?php
                        echo CHtml::label(gT("Bypass date control before sending email:"), 'bypassdatecontrol', [
                            'title' => gT("If some participants have a 'valid from' date set which is in the future, they will not be able to access the survey before that 'valid from' date."),
                            'unescaped' => 'unescaped',
                            'class' => 'form-label '
                        ]);
                        ?>
                        <div class=''>
                            <?php
                            $this->widget('yiiwheels.widgets.switch.WhSwitch', [
                                'name' => "bypassdatecontrol",
                                'id' => "bypassdatecontrol",
                                'value' => '0',
                                'onLabel' => gT('On'),
                                'offLabel' => gT('Off')
                            ]);
                            ?>
                        </div>
                        <div class=''></div>
                    </div>

                    <!-- Only partial responses -->
                    <div class='form-group'>
                        <label class='form-label '
                               for='partialonly'><?php eT("Send email only to participants with partial responses:"); ?></label>
                        <div class=''>
                            <?php
                            $this->widget('yiiwheels.widgets.switch.WhSwitch', [
                                'name' => "partialonly",
                                'id' => "partialonly",
                                'value' => '0',
                                'onLabel' => gT('On'),
                                'offLabel' => gT('Off')
                            ]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <!-- Max reminders -->
                    <div class='form-group'>
                        <label class='form-label ' for='maxremindercount'><?php eT("Max reminders:"); ?></label>
                        <div class=''>
                            <input type="text" id="maxremindercount" class="form-control" size="25" value=""
                                   name="maxremindercount" style="width: 50%;"/>
                        </div>
                    </div>

                    <!-- Min days between reminders -->
                    <div class='form-group'>
                        <label class='form-label '
                               for='minreminderdelay'><?php eT("Min days between reminders:"); ?></label>
                        <div class=''>
                            <input type="text" id="minreminderdelay" class="form-control" size="25" value=""
                                   name="minreminderdelay" style="width: 50%;">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <ul class="nav nav-tabs">
                    <?php $c = true ?>
                    <?php foreach ($oSurvey->allLanguages as $language): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $c ? "active" : "" ?>" data-bs-toggle="tab" href="#<?= $language ?>">
                                <?php if ($c) {
                                    $c = false;
                                } ?>
                                <?= getLanguageNameFromCode($language, false) . " " . (($language == $oSurvey->language) ? "(" . gT("Base language") . ")" : "") ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php
                    $c = true;
                    foreach ($surveylangs as $language) {
                        $fieldsarray["{ADMINNAME}"] = $thissurvey['adminname'];
                        $fieldsarray["{ADMINEMAIL}"] = $thissurvey['adminemail'];
                        $fieldsarray["{SURVEYNAME}"] = $thissurvey[$language]['name'];
                        $fieldsarray["{SURVEYDESCRIPTION}"] = $thissurvey[$language]['description'];
                        $fieldsarray["{EXPIRY}"] = $thissurvey["expiry"];

                        $subject = Replacefields($thissurvey[$language]['email_remind_subj'], $fieldsarray, false);
                        $textarea = Replacefields($thissurvey[$language]['email_remind'], $fieldsarray, false);
                        if ($ishtml !== true) {
                            $textarea = str_replace(array('<x>', '</x>'), array(''), $textarea); // ?????
                        }
                        ?>

                        <div id="<?php echo $language; ?>" class="tab-pane fade <?php if ($c) {
                            $c = false;
                            echo 'show active';
                        } ?>">

                            <div class='form-group'>
                                <label class='form-label '
                                       for='from_<?php echo $language; ?>'><?php eT("From:"); ?></label>
                                <div class=''>
                                    <?php echo CHtml::textField("from_{$language}", $thissurvey[$baselang]['adminname'] . " <" . $thissurvey[$baselang]['adminemail'] . ">", array('class' => 'form-control')); ?>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='form-label '
                                       for='subject_<?php echo $language; ?>'><?php eT("Subject:"); ?></label>
                                <div class=''>
                                    <?php echo CHtml::textField("subject_{$language}", $subject, array('class' => 'form-control')); ?>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='form-label '
                                       for='message_<?php echo $language; ?>'><?php eT("Message:"); ?></label>
                                <div class="htmleditor ">
                                    <?php echo CHtml::textArea("message_{$language}", $textarea, array('cols' => 80, 'rows' => 20, 'class' => 'form-control')); ?>
                                    <?php echo getEditor("email-reminder", "message_$language", "[" . gT("Reminder Email:", "js") . "](" . $language . ")", $surveyid, '', '', "tokens"); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="row">
                    <div class='form-group'>
                        <div class=''></div>
                        <div class=''>
                            <?php echo CHtml::submitButton(gT("Send Reminders", 'unescaped'), array('class' => 'btn btn-outline-secondary')); ?>
                        </div>

                        <?php
                        echo CHtml::hiddenField('ok', 'absolutely');
                        echo CHtml::hiddenField('subaction', 'remind');
                        if (!empty($tokenids)) {
                            echo CHtml::hiddenField('tokenids', implode('|', (array)$tokenids));
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php echo CHtml::endForm() ?>
        </div>

        <?php
        App()->getClientScript()->registerScript("Tokens:BindReminderView", "
        LS.renderBootstrapSwitch();
        $('#send-reminders-button').on('click', function(){
            $('#sendreminder').submit();
        })
", LSYii_ClientScript::POS_POSTSCRIPT);
        ?>
