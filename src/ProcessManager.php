<?php
namespace SimilarTransactions;
abstract class ProcessManager implements \Countable
{
    protected $pids = array();
    const PROCESSCOUNT = 50;
    protected $queue = false;
    protected $token = false;
    protected $mypid;
    protected $isChild = false;
    function __construct()
    {
    }

    function count()
    {
        return count($this->pids);
    }

    function waitForChildren()
    {
        $count = count($this->pids);
        do {
            if ($count > count($this->pids)) {
                $count = count($this->pids);
            }
            reset($this->pids);
            \pcntl_wait($status, WNOHANG OR WUNTRACED);
            while (list($key, $val) = each($this->pids)) {
                if(!\posix_kill($val, 0)) {
                    unset($this->pids[$key]);
                }
            }
            $this->pids = array_values($this->pids); // Reindex the array
        } while (count($this->pids));
    }

    function go()
    {
        $this->queue = msg_get_queue($this->token = ftok(__FILE__, 'a'));
        do {
            if (count($this->pids) == static::PROCESSCOUNT) continue;
            if ($this->isChild) break;
            $this->childSetup();
            $pid = \pcntl_fork();
            if ($this->handlePid($pid)) break;
        } while (1);
        msg_remove_queue($this->queue);
    }

    function handlePid($pid)
    {
        if (!$pid) {
            // child process
            $this->isChild = true;
            $this->child();
            exit;
        } elseif (-1 == $pid) {
            // could not fork, so fail silently
            echo "ERROR: fork failed\n";
            die;
        } else {
            // parent
            $this->pids[] = $pid;
            \pcntl_wait($status, WNOHANG OR WUNTRACED);
            while (list($key, $val) = each($this->pids)) {
                if(!\posix_kill($val, 0)) {
                    unset($this->pids[$key]);
                }
            }
            $this->pids = array_values($this->pids); // Reindex the array
            if ($this->parent()) return true;
        }
    }

    abstract function childSetup();

    abstract function parent();

    function child()
    {
        // do nothing
        exit;
    }
}
