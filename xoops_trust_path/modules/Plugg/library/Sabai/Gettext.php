<?php
class Sabai_Gettext
{
    protected $_defaultDomain = '';
    protected $_messages = array();
    protected $_pluralFuncs = array();
    protected $_pluralFuncExpressions = array();

    public function textdomain($domain)
    {
        $this->_defaultDomain = $domain;
    }

    public function bindtextdomain($domain, $directory, $file = null)
    {
        // Resolve path to MO file
        $mo_file = $this->_getGettextFilePath($directory, $domain, $file);

        // Default plural func expression
        $plural_func_expr = 'return $n == 1 ? 0 : 1;';

        // Generate array of localized messages from gettext mo file
        if (file_exists($mo_file)) {
            if (!class_exists('File_Gettext', false)) {
                require 'File/Gettext.php';
            }
            $gettext = File_Gettext::factory('MO', $mo_file);

            if (true == $result = $gettext->load()) {
                $gettext_array = $gettext->toArray();
                $messages = array();
                foreach ($gettext_array['strings'] as $key => $value) {
                    // Plurable messages have all strings separated by a null string
                    if (strpos($key, chr(0))) {
                        $key = array_shift(explode(chr(0), $key));
                        $value = explode(chr(0), $value);
                    }
                    $messages[$key] = $value;
                }

                $this->_messages[$domain] = $messages;

                if (isset($gettext_array['meta']['Plural-Forms'])) {
                    $plural_forms = explode(';', $gettext_array['meta']['Plural-Forms']);
                    if (!empty($plural_forms[1])) {
                        $expression = str_replace(array('plural', 'n'), array('$plural', '$n'), $plural_forms[1]) . '; return $plural;';

                        // Test the expression to see if it is valid php
                        $n = 1;
                        if ((false !== $ret = @eval($expression)) && is_int($ret)) {
                            // Use the expression
                            $plural_func_expr = $expression;
                        }
                    }
                }

                $this->_pluralFuncs[$domain] = create_function('$n', $plural_func_expr);
                $this->_pluralFuncExpressions[$domain] = $plural_func_expr;

                return true;
            }
        }

        // No MO file
        $this->_messages[$domain] = array();
        $this->_pluralFuncs[$domain] = create_function('$n', $plural_func_expr);
        $this->_pluralFuncExpressions[$domain] = $plural_func_expr;

        return false;
    }

    public function gettext($message)
    {
        if ($ret = $this->hastext($message)) return $ret;

        return $message;
    }

    public function dgettext($domain, $message)
    {
        if ($ret = $this->dhastext($domain, $message)) return $ret;

        return $message;
    }

    public function ngettext($message1, $message2, $num)
    {
        if (($message = $this->hastext($message1)) && is_array($message)) {
            $index = $this->_pluralFuncs[$this->_defaultDomain]($num);
            if (isset($message[$index])) return $message[$index];
        }

        return $num == 1 ? $message1 : $message2;
    }

    public function dngettext($domain, $message1, $message2, $num)
    {
        if (($message = $this->dhastext($domain, $message1)) && is_array($message)) {
            $index = $this->_pluralFuncs[$domain]($num);
            if (isset($message[$index])) return $message[$index];
        }

        return $num == 1 ? $message1 : $message2;
    }

    public function _($message)
    {
        return $this->gettext($message);
    }

    protected function _getGettextFilePath($directory, $domain, $file = null)
    {
        return sprintf(
            '%s/%s/%s/%s',
            $directory, SABAI_LANG, SABAI_CHARSET, isset($file) ? $file : $domain . '.mo'
        );
    }

    public function getMessages($domain = null)
    {
        if (!isset($domain)) $domain = $this->_defaultDomain;

        return $this->_messages[$domain];
    }

    public function countMessages($domain = null)
    {
        if (!isset($domain)) $domain = $this->_defaultDomain;

        return count($this->_messages[$domain]);
    }

    public function hastext($message)
    {
        return isset($this->_messages[$this->_defaultDomain][$message])
            ? $this->_messages[$this->_defaultDomain][$message]
            : false;
    }

    public function dhastext($domain, $message)
    {
        return isset($this->_messages[$domain][$message])
            ? $this->_messages[$domain][$message]
            : false;
    }
}