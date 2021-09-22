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
        if (self::getInstance()->attributes['accessToken'] === $token) {
            return new static(self::getInstance()->attributes);
        }
    }

    public static function findIdentity($id)
    {
        // just ignore the $id param here
        return new static(self::getInstance()->attributes);
    }

    public function getId()
    {
        return $this->attributes['id'];
    }

    public function getAuthKey()
    {
        return $this->attributes['authKey'];
    }

    public function validateAuthKey($authKey)
    {
        return $this->attributes['authKey'] === $authKey;
    }

    public function toArray()
    {
        return $this->attributes;
    }
}
