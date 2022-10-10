<?php

namespace LimeSurvey\Api;

class ApiSession
{
    const INVALID_SESSION_KEY = 'Invalid session key';

    /**
     * Login with username and password
     *
     * @access public
     * @param string $sUsername username
     * @param string $sPassword password
     * @param string $sPlugin plugin to be used
     * @return bool|string
     */
    public function doLogin($sUsername, $sPassword, $sPlugin = 'Authdb')
    {
        /* @var $identity LSUserIdentity */
        $identity = new \LSUserIdentity($sUsername, $sPassword);
        $identity->setPlugin($sPlugin);
        $event = new \PluginEvent('remoteControlLogin');
        $event->set('identity', $identity);
        $event->set('plugin', $sPlugin);
        $event->set('username', $sUsername);
        $event->set('password', $sPassword);
        App()->getPluginManager()->dispatchEvent($event, array($sPlugin));
        if (!$identity->authenticate()) {
            if ($identity->errorMessage) {
                // don't return an empty string
                return $identity->errorMessage;
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * Fills the session with necessary user info on the fly
     *
     * @access public
     * @param string $username The username
     * @return bool
     */
    public function jumpStartSession($username)
    {
        $aUserData = \User::model()->findByAttributes(array('users_name' => (string) $username))->attributes;

        $session = array(
            'loginID' => intval($aUserData['uid']),
            'user' => $aUserData['users_name'],
            'full_name' => $aUserData['full_name'],
            'htmleditormode' => $aUserData['htmleditormode'],
            'templateeditormode' => $aUserData['templateeditormode'],
            'questionselectormode' => $aUserData['questionselectormode'],
            'dateformat' => $aUserData['dateformat'],
            'adminlang' => 'en'
        );
        foreach ($session as $k => $v) {
            \Yii::app()->session[$k] = $v;
        }
        \Yii::app()->user->setId($aUserData['uid']);

        return true;
    }

    /**
     * Check if the session key is valid. If yes returns true, otherwise false and sends an error message with error code 1
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @return bool
     */
    public function checkKey($sSessionKey)
    {
        $sSessionKey = (string) $sSessionKey;
        $criteria = new \CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        \Session::model()->deleteAll($criteria);
        $oResult = \Session::model()->findByPk($sSessionKey);

        if (is_null($oResult)) {
            return false;
        } else {
            $this->jumpStartSession($oResult->data);
            return true;
        }
    }
}