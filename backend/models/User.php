<?php

namespace backend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use borales\extensions\phoneInput\PhoneInputValidator;
use common\models\Role;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property int $role
 * @property int $institution_id
 * @property string $username
 * @property string|null $email
 * @property string $password
 * @property string $auth_key
 * @property string|null $password_reset_token
 * @property string|null $verification_token
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Institutions $institution
 * @property Role $role0
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface {

    const STATUS_DELETED = 2;
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_OUT_OF_OFFICE = 8;

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'aauth_users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['role', 'username', 'first_name', 'last_name', 'auth_key', 'email', 'user_type'], 'required'],
            [['role', 'status', 'district_id', 'province_id'], 'integer'],
            [['username', 'email', 'first_name', 'last_name',
            'password', 'auth_key', 'password_reset_token', 'verification_token'], 'string', 'max' => 255],
            [['user_type'], 'safe'],
//            ['district_id', 'required', 'when' => function($model) {
//                    return ($model->user_type == "District" ? true : false);
//                }, 'whenClient' => "function (attribute, value) {
//                    return $('input[type=\"select\"][name=\"Users[user_type]\"]:selected').val() == 'District';
//                }"
//            ],
            [['district_id'], 'required', 'when' => function($model) {
                    return $this->user_type == "District" ? true : false;
                }, 'whenClient' => "function (attribute, value) {
                   return $('input[type=\"select\"][name=\"Users[user_type]\"]:selected').val() == 'District';
              }"
            ],
            [['province_id'], 'required', 'when' => function($model) {
                    return $this->user_type === "Province" ? true : false;
                }, 'whenClient' => "function (attribute, value) {
                   return $('input[type=\"select\"][name=\"Users[user_type]\"]:selected').val() == 'Province';
              }"
            ],
            // [['phone'], PhoneInputValidator::className()],
            ['email', 'email', 'message' => "The email isn't correct!"],
            ['email', 'unique', 'when' => function($model) {
                    return $model->isAttributeChanged('email');
                }, 'message' => 'Email already in use!'],
            [['role'], 'exist', 'skipOnError' => true, 'targetClass' => Role::className(), 'targetAttribute' => ['role' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'role' => 'Role',
            'username' => 'Username',
            // 'other_name' => 'Other names',
            'first_name' => 'First name',
            'last_name' => 'Surname',
            'email' => 'Email',
            'password' => 'Password',
            'auth_key' => 'Auth Key',
            'district_id' => "District",
            'province_id' => "Province",
            'user_type' => "User type",
            'password_reset_token' => 'Password Reset Token',
            'verification_token' => 'Verification Token',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * Gets query for [[Role0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole0() {
        return $this->hasOne(Role::className(), ['id' => 'role']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id) {
        // return static::find()->cache(3600)->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->one();
        return static::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->one();
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username) {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findById($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token,
                    'status' => self::STATUS_ACTIVE,
        ]);
    }

    public static function findByPasswordResetTokenInactiveAccount($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token,
                    'status' => self::STATUS_INACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
                    'verification_token' => $token,
                    'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password . $this->auth_key, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password) {
        $this->password = Yii::$app->security->generatePasswordHash($password . $this->auth_key);
    }

    public function setStatus() {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * Generates "remember me" authentication key
     * @throws \yii\base\Exception
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     * @throws \yii\base\Exception
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * @throws \yii\base\Exception
     */
    public function generateEmailVerificationToken() {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    public static function userIsAllowedTo($right) {
        $session = Yii::$app->session;
        $rights = explode(',', $session['rights']);
        if (in_array($right, $rights)) {
            return true;
        }
        return false;
    }

    public function getFullName() {
        return ucfirst(strtolower($this->first_name)) . " " . ucfirst(strtolower($this->last_name));
    }

    /**
     * @return array
     */
    public static function getOtherNames() {
        $users = self::find()->orderBy(['other_name' => SORT_ASC])->all();
        $list = ArrayHelper::map($users, 'other_name', 'other_name');
        return $list;
    }

    /**
     * @return array
     */
    public static function getFirstname() {
        $users = self::find()->orderBy(['first_name' => SORT_ASC])->all();
        $list = ArrayHelper::map($users, 'first_name', 'first_name');
        return $list;
    }

    /**
     * @return array
     */
    public static function getLastNames() {
        $users = self::find()->orderBy(['last_name' => SORT_ASC])->all();
        $list = ArrayHelper::map($users, 'last_name', 'last_name');
        return $list;
    }

    /**
     * @return array
     */
    public static function getUsernames() {
        $users = self::find()
                        ->orderBy(['username' => SORT_ASC])->all();
        $list = ArrayHelper::map($users, 'username', 'username');
        return $list;
    }

    /**
     * @return array
     */
    public static function getEmails() {
        $users = self::find()
                        ->orderBy(['email' => SORT_ASC])->all();
        $list = ArrayHelper::map($users, 'email', 'email');
        return $list;
    }

    public static function getUserTypes() {
        $users = self::find()
                        ->orderBy(['user_type' => SORT_ASC])->all();
        $list = ArrayHelper::map($users, 'user_type', 'user_type');
        return $list;
    }

    /**
     * 
     * @return type array
     */
    public static function getFullNames() {
        $query = static::find()
                ->select(["CONCAT(first_name,' ',last_name) as name", 'first_name'])
                //->where(["IN", 'status', [self::STATUS_ACTIVE]])
                ->orderBy(['id' => SORT_ASC])
                ->asArray()
                ->all();

        return \yii\helpers\ArrayHelper::map($query, 'first_name', 'name');
    }

    /**
     * @return array
     */
    public static function getActiveUsers() {
        $query = static::find()
                ->select(["CONCAT(first_name,' ',last_name) as name", 'id'])
                ->where(['status' => self::STATUS_ACTIVE])
                // ->andWhere(['NOT IN', 'first_name', 'Board'])
                ->orderBy(['username' => SORT_ASC])
                ->asArray()
                ->all();
        return ArrayHelper::map($query, 'id', 'name');
    }

}
