<?php
namespace SimilarTransactions;
class SMGrabber
{
    protected $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31 (CelloG\'s Similar Transaction Script (0.1.0))';
    protected $cookies = '';
    protected $downloadcount = 0;
    protected $loggedin = false;
    protected $main;
    function __construct(Main $main)
    {
        $this->main = $main;
    }

    function download($url, $failed = false)
    {
        if ($downloadcount == 4995 || !$this->loggedin) {
            $this->relogin();
        }
        $context = stream_context_create(array('http' => array(
            'follow_location' => 0,
            'user_agent' => $this->useragent,
            'header' => 'Cookie: ' . $this->processCookies()
        )));
        $fp = fopen($url, 'r', false, $context);
        $info = stream_get_meta_data($fp);
        if (strpos($info['wrapper_data'][0], '302')) {
            if ($failed) {
                die("Error: we were unable to login, check to make sure gunipig isn't blocked\n");
            }
            fclose($fp);
            $this->loggedin = false;
            return $this->download($url, true); // try again, with a login
        }
        $this->getCookies($info['wrapper_data']);
        $ret = stream_get_contents($fp);
        fclose($fp);
        return $ret;
    }

    protected function processCookies()
    {
        $cookie = array();
        foreach ($this->cookies as $name => $value) {
            $cookie[] = $name . '=' . $value;
        }
        return implode('; ', $cookie);
    }

    protected function getCookies($data)
    {
        foreach ($data as $line) {
            if (strpos($line, 'Set-Cookie:') === 0) {
                // extract the cookies
                $line = str_replace('Set-Cookie: ', '', $line);
                $line = explode(';', $line);
                $line = trim($line[0]);
                $line = explode('=', $line);
                //if (isset($this->cookies[$line[0]])) continue;
                $this->cookies[$line[0]] = $line[1];
            }
        }
    }

    function relogin()
    {
        // TODO: check to see if the database cookie is different from ours, and if so, use it instead
        $logout = array(
            'user_agent' => $this->useragent,
        );
        if ($this->cookies) {
            $logout['header'] = 'Cookie: ' . $this->cookies;
        }
        $context = stream_context_create(array('http' => $logout));
        file_get_contents('http://en.strikermanager.com/logout.php', false, $context);
        $this->cookies = array();
        $login = http_build_query(array(
                'alias' => $this->main->getLogin(),
                'pass' => $this->main->getPassword(),
                'dest' => ''
            ));
        $context = stream_context_create(array('http' => array(
            'method' => 'POST',
            'follow_location' => 0,
            'user_agent' => $this->useragent,
            'header' => array(
                'Content-type: application/x-www-form-urlencoded',
                'Content-length: ' . strlen($login),
            ),
            'content' => $login
        )));
        $fp = fopen('http://en.strikermanager.com/loginweb.php', 'r', false, $context);
        $info = stream_get_meta_data($fp);
        $this->getCookies($info['wrapper_data']);
        fclose($fp);
        $context = stream_context_create(array('http' => array(
            'user_agent' => $this->useragent,
            'header' => array(
                'Cookie: ' . $this->processCookies()
            )
        )));
        $fp = fopen('http://en.strikermanager.com/inicio.php', 'r', false, $context);
        $info = stream_get_meta_data($fp);
        $this->getCookies($info['wrapper_data']);
        // TODO: update the database with new cookie somehow, so others can pull it in too.
        fclose($fp);
        $this->loggedin = true;
    }
}
?>