<?php
namespace SimilarTransactions;
include 'Player/HistoryParser.php';
include 'Player.php';
include 'Team.php';
class SMGrabber
{
    protected $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31 (CelloG\'s Similar Transaction Script (0.1.0))';
    protected $cookies = '';
    protected $downloadcount = 0;
    protected $loggedin = false;
    function download($url)
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
                'alias' => 'gunipig',
                'pass' => 'flomflom934',
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
        fclose($fp);
        $this->loggedin = true;
    }
}
$a = new SMGrabber;
$league = $a->download('http://en.strikermanager.com/liga.php?id_liga=51210');
preg_match_all('/equipo\.php\?id=(\d+)/', $league, $matches);
foreach($matches[1] as $team) {
    $team = new Team($team);
    $team->getSquad($a);
}
?>