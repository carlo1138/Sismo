<?php

/*
 * This file is part of the Sismo utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sismo\Contrib;

use Sismo\Notifier\Notifier;
use Sismo\Commit;

// @codeCoverageIgnoreStart
/**
 * Notifies builds via a IRC server.
 * 
 * @author Carlo Forghieri <carlo.forghieri@gmail.com>
 */
class IrcNotifier extends Notifier
{   
    protected $server;
    protected $channel;
    protected $port;
    protected $nickname;
    protected $format;

    /**
     * Constructor.
     * 
     * @param string $server
     * @param string $channel
     * @param integer $port
     * @param string $password
     * @param string $nickname
     */
    public function __construct($server, $channel, $password = null, $port = 6667, $nickname = 'sismobot',  $format = '[%STATUS%] %name% %short_sha% -- %message% by %author%')
    {
        $this->server = $server;
        $this->port = $port;
        $this->channel = $channel;
        $this->password = $password;
        $this->nickname = $nickname;
        $this->format = $format;
    }

    public function notify(Commit $commit)
    {
        $this->sendMessage($this->format($this->format, $commit));
    }

    /**
     * Connect the message via IRC.
     * 
     * @param string $message
     * @todo Find a nice OOP 5.x IRC client library
     */
    protected function sendMessage($message)
    {
        $commands = array(
            sprintf("USER %s %s %s :%s",
                    $this->nickname, 0, 'localhost', $this->nickname),
            sprintf("NICK %s",
                    $this->nickname),
            sprintf("JOIN #%s%s",
                    $this->channel, trim(' ' . $this->password)),
            sprintf("PRIVMSG #%s :%s",
                    $this->channel, $message),
            "QUIT"
        );
        $con = fsockopen($this->server, $this->port);
        while ($con) {
            $command = array_shift($commands);
            if (!$command) {
                break;
            }
            if (!fwrite($con, $command . "\r\n")) {
                break;
            }
        }
        while ($data = fgets($con, 128)) {}
        fclose($con);
    }
}
// @codeCoverageIgnoreEnd