<?php

/*
 * This file is part of the React Symfony Server package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\SymfonyReactServer;

/**
 * Class ConsoleMessage.
 */
class ConsoleMessage implements Printable
{
    protected $url;
    protected $method;
    protected $code;
    protected $message;
    protected $elapsedTime;

    /**
     * Message constructor.
     *
     * @param string $url
     * @param string $method
     * @param int    $code
     * @param string $message
     * @param int    $elapsedTime
     */
    public function __construct(
        string $url,
        string $method,
        int $code,
        string $message,
        int $elapsedTime
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->code = $code;
        $this->message = $message;
        $this->elapsedTime = $elapsedTime;
    }

    /**
     * Print.
     */
    public function print()
    {
        $method = str_pad($this->method, 6, ' ');
        $color = '32';
        if ($this->code >= 300 && $this->code < 400) {
            $color = '33';
        } elseif ($this->code >= 400) {
            $color = '31';
        }

        echo "\033[01;{$color}m".$this->code."\033[0m";
        echo " $method $this->url ";
        echo "(\e[00;37m".$this->elapsedTime.' ms | '.((int) (memory_get_usage() / 1000000))." MB\e[0m)";
        if ($this->code >= 300) {
            echo " - \e[00;37m".$this->messageInMessage($this->message)."\e[0m";
        }
        echo PHP_EOL;
    }

    /**
     * Find message.
     *
     * @param string $message
     *
     * @return string
     */
    private function messageInMessage(string $message): string
    {
        $decodedMessage = json_decode($message, true);
        if (
            is_array($decodedMessage) &&
            isset($decodedMessage['message']) &&
            is_string($decodedMessage['message'])
        ) {
            return $decodedMessage['message'];
        }

        return $message;
    }
}