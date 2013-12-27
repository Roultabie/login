<?php
/**
 * Login lib
 */

class user
{

    private $username;
    private $mail;
    private $description;
    private $ip;
    private $userAgent;

    function __construct()
    {
        $this->setUsername('');
        $this->setLevel('');
        $this->setIp('');
        $this->setUserAgent('');
        $this->setConfig(array());
    }

    function getUsername()
    {
        return $this->username;
    }

    function setUsername($username)
    {
        $this->username = $username;
    }

    function getLevel()
    {
        return $this->level;
    }

    function setLevel($level)
    {
        $this->level = $level;
    }

    function getIp()
    {
        return $this->ip;
    }

    function setIp($ip)
    {
        $this->ip = $ip;
    }

    function getUserAgent()
    {
        return $this->userAgent;
    }

    function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    function getConfig()
    {
        return $this->config;
    }

    function setConfig($config)
    {
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                if ($key !== 'hash' && $key !== 'salt' && $key !== 'level') {
                    $this->config[$key] = $value;
                }
            }
        }
    }
}


/**
 * $config['users'] format : $users['username'] = array('hash' => 'hash', 'salt' => 'salt', 'level' => 'level', 'mail' => 'mail', 'description' => 'description', etc ...);
 */
class userWriter
{
    private $users;
    private $hash;
    private $hashMethod;

    function __construct()
    {
        $this->users      = $GLOBALS['config']['users'];
        $this->hashMethod = 'sha1';
    }

    public function loginCheck($login = "", $password = "") // pour utiliser une autre méthode (sql ...) faire hériter une nouvelle classe et redéfinir cette méthode
    {
        if (!empty($login) && !empty($password)) {
            if (array_key_exists($login, $this->users)) {
                $elements = $this->users[$login];
                if ($elements['hash'] === self::returnHash($password, $elements['salt'], $this->hashMethod)) {
                    $user = new user();
                    $user->setUsername($login);
                    $user->setLevel($elements['level']);
                    $user->setIp($_SERVER['REMOTE_ADDR']);
                    $user->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                    $user->setConfig($elements);
                    $_SESSION['userDatas'] = serialize($user);
                    $_SESSION['lastTime']  = microtime(TRUE);
                    return $user;
                }
                else {
                    unset($user); // On ne sait jamais ;)
                    return FALSE;
                }
            }
        }
        elseif(is_object(unserialize($_SESSION['userDatas']))) {
            $user     = unserialize($_SESSION['userDatas']);
            $lastHash = hash('sha256', $user->getIp() . $user->getUserAgent());;
            $currHash = hash('sha256', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
            if ($lastHash === $currHash) {
                $currentTime = microtime(TRUE);
                $breakTime   = $currentTime - $_SESSION['lastTime'];
                if ($breakTime < $GLOBALS['config']['sessionExpire']) {
                    $_SESSION['lastTime'] = $currentTime;
                    return $user;
                }
                else {
                    unset($user);
                    return FALSE;
                }
            }
            else {
                unset($user);
                return FALSE;
            }
        }
        else {
            unset($user);
            return FALSE;
        }
    }

    public static function initSession()
    {
        session_start();
        $_SESSION['startTime'] = microtime(TRUE);
    }

    public static function killSession()
    {
        session_destroy();
    }

    public static function generateUser($username, $password, $hashMethod)
    {
        if (is_string($username)) {
            $salt            = sha1(uniqid('',true) . mt_rand() . base64_encode(mt_rand()));
            $hash            = self::returnHash($password, $salt, $hashMethod);
            $user[$username] = array('hash' => $hash, 'salt' => $salt);
        }
        return var_export($user);
    }

    private static function returnHash($password, $salt, $hashMethod)
    {
        $hash = hash($hashMethod, $salt . $password . strrev($salt));
        return $hash;
    }
}

userWriter::initSession();
$session = new userWriter();

if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    $user = $_POST['login'];
    $pass = $_POST['pass'];
}

$user = $session->loginCheck($user, $pass);

if (!is_object($user) || $_POST['disconnect'] === '1' || $_GET['disconnect'] === '1') {
    if ($_GET['disconnect'] === '1') $loginAction = 'action="' . $_SERVER['SCRIPT_NAME'] . '"';
    userWriter::killSession();
    require 'loginform.php';
    exit();
}

?>
