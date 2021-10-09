<?php

namespace app\components;

use yii\helpers\Json;
use codemix\streamlog\Target as Streamlog;
use yii\log\Logger;
use Yii;

/**
 * A log target for streams in URL format.
 */
class JsonStreamTarget extends Streamlog
{
    /**
     * Formats a log message for display as a string.
     * @param array $message the log message to be formatted.
     * The message structure follows that in [[Logger::messages]].
     * @return string the formatted message
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;

        $logData = [];

        // Begin assembling the data that we will JSON-encode for the log.
        $logData['timestamp'] = $this->getTime($timestamp);

        $logData['user_id'] =  Yii::$app->user?->id ?? '-';
        $logData['username'] =  Yii::$app->user?->identity?->username ?? '-';

        $logData['ip'] = $_SERVER['HTTP_X_ORIGINAL_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? Yii::$app->getRequest()?->getUserIP();

        $logData['level'] = Logger::getLevelName($level);
        $logData['category'] = $category;
        $logData['message'] = $this->extractMessageContentData($text);

        // Format the data as a JSON string and return it.
        return Json::encode($logData);
    }

    /**
     * If the given prefix is a JSON string with key-value data, extract it as
     * an associative array. Otherwise return null.
     *
     * @param mixed $prefix The raw prefix string.
     * @return null|array
     */
    protected function extractPrefixKeyValueData($prefix)
    {
        $result = null;

        if ($this->isJsonString($prefix)) {

            // If it has key-value data, as evidenced by the raw prefix string
            // being a JSON object (not JSON array), use it.
            if (substr($prefix, 0, 1) === '{') {
                $result = Json::decode($prefix);
            }
        }

        return $result;
    }

    /**
     * Determine whether the given value is a string that parses as valid JSON.
     *
     * @param mixed $string The value to check.
     * @return boolean
     */
    protected function isJsonString($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Extract the message content data into a more suitable format for
     * JSON-encoding for the log.
     *
     * @param mixed $text The message content, which could be a
     *     string, array, exception, or other data type.
     * @return mixed The extracted data.
     */
    protected function extractMessageContentData($text)
    {
        $result = null;

        if ($text instanceof \Exception) {

            // Handle log messages that are exceptions a little more
            // intelligently.
            //
            // NOTE: In our limited testing, this is never used. Apparently
            //       something is converting the exceptions to strings despite
            //       the statement at
            //       http://www.yiiframework.com/doc-2.0/yii-log-logger.html#$messages-detail
            //       that the data could be an exception instance.
            //
            $result = array(
                'code' => $text->getCode(),
                'exception' => $text->getMessage(),
            );

            if ($text instanceof \yii\web\HttpException) {
                $result['statusCode'] = $text->statusCode;
            }
        } elseif ($this->isMultilineString($text)) {

            // Split multiline strings (such as a stack trace) into an array
            // for easier reading in the log.
            $result = explode("\n", $text);
        } else {

            // Use anything else as-is.
            $result = $text;
        }

        return $result;
    }

    /**
     * Determine whether the given data is a string that contains at least one
     * line feed character ("\n").
     *
     * @param mixed $data The data to check.
     * @return boolean
     */
    protected function isMultilineString($data)
    {
        if (!is_string($data)) {
            return false;
        } else {
            return (strpos($data, "\n") !== false);
        }
    }
}
