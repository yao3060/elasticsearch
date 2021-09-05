<?php

namespace app\models;

class GenericUser implements \yii\web\IdentityInterface
{
    private static $instance = null;
    /**
     * All of the user's attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Create a new generic User object.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (self::getInstance()->attributes['access_token'] === $token) {
            return new static(self::getInstance()->attributes);
        }

        return null;
    }

    public static function findIdentity($id)
    {
        return self::getInstance()->attributes;
    }

    public function getId()
    {
        return $this->attributes['id'];
    }

    public function getAuthKey()
    {
        return $this->attributes['auth_key'];
    }

    public function validateAuthKey($authKey)
    {
        return $this->attributes['auth_key'] === $authKey;
    }
}
