<?php
/**
 * Login lib
 */

class user
{

    private $username;
    private $ip;
    private $userAgent;
    private $config;

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
    private static $statusCodes;
    private static $status;

    function __construct()
    {
        $this->users         = $GLOBALS['config']['users'];
        $this->hashMethod    = 'sha1';
        $this->sessionExpire = $GLOBALS['config']['sessionExpire'];
        self::$statusCodes   = array(1  => 'users-not-initialized',
                                     2  => 'user-password-false',
                                     3  => 'sessionExpire-not-initialized',
                                     4  => 'user-password-false',
                                     5  => 'user-password-false',
                                     6  => 'session-expired',
                                     51 => 'login-successful',
                                     52 => 'user-control-successful');
        // self::$statusCodes   = array(0  => 'general-error',
        //                              1  => 'users-not-initialized',
        //                              2  => 'user-not-found',
        //                              3  => 'sessionExpire-not-initialized',
        //                              4  => 'password-false',
        //                              5  => 'session-usurped',
        //                              6  => 'session-expired',
        //                              50 => 'login-started',
        //                              51 => 'login-successful',
        //                              52 => 'user-control-successful');
    }

    public function loginCheck($login = "", $password = "") // pour utiliser une autre méthode (sql ...) faire hériter une nouvelle classe et redéfinir cette méthode
    {
        if (!empty($login) && !empty($password)) {
            if (is_array($this->users)) {
                if (array_key_exists($login, $this->users)) {
                    if (is_int($this->sessionExpire)) {
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
                            self::setStatus(51);
                            return $user;
                        }
                        else {
                            self::setStatus(4);
                            return false;
                        }
                    }
                    else {
                        self::setStatus(3);
                        return false;
                    }
                }
                else {
                    self::setStatus(2);
                    return false;
                }
            }
            else {
                self::setStatus(1);
                return false;
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
                    $this->justLogged     = false;
                    self::setStatus(52);
                    return $user;
                }
                else {
                    unset($user);
                    self::setStatus(6);
                    return false;
                }
            }
            else {
                unset($user);
                self::setStatus(5);
                return false;
            }
        }
        else {
            self::setStatus(50);
            return false;
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

    public static function disconnect()
    {
        self::setStatus(52);
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

    public static function getStatus()
    {
        return self::$status;
    }

    private static function setStatus($int)
    {
        if (array_key_exists($int, self::$statusCodes)) {
            self::$status = array('code' => $int, 'message' => self::$statusCodes[$int]);
        }
        else {
            self::$status = false;
        }
    }
}

userWriter::initSession();
$session = new userWriter();
if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    $user = $_POST['login'];
    $pass = $_POST['pass'];
}

$user = $session->loginCheck($user, $pass);

if (!is_object($user)) {
    $status = userWriter::getStatus();
    userWriter::killSession();
    $statusClass   = ($status['code'] < 50) ? "error" : "success";
    $statusMessage = $status['message'];
    require 'loginform.php';
    exit();
}
else {
    if (isset($_POST['disconnect']) || isset($_GET['disconnect'])) {
        $url = preg_replace('/(.*)(?|&)disconnect={0,1}[^&]+?(&)(.*)/i', '$1$2$4', $_SERVER['PHP_SELF'] . '&'); 
        $url = substr($url, 0, -1);
        userWriter::disconnect();
        header('Location: ' . $url);
        exit();
    }
    $status = userWriter::getStatus();
    if ($status['code'] === 51) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>
