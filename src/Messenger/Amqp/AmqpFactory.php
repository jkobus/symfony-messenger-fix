<?php

declare(strict_types=1);

namespace App\Messenger\Amqp;

use Symfony\Component\Messenger\Transport\AmqpExt\AmqpFactory as SymfonyAmqpFactory;

class AmqpFactory extends SymfonyAmqpFactory
{
    private array $connectionPool = [];
    private int $openedChannels = 0;

    public function createConnection(array $credentials): \AMQPConnection
    {
        $connectionId = md5(json_encode($credentials, JSON_THROW_ON_ERROR));

        if (!isset($this->connectionPool[$connectionId])) {
            $connection = new \AMQPConnection($credentials);
            $this->connectionPool[$connectionId] = $connection;
        }

        return $this->connectionPool[$connectionId];
    }

    public function createChannel(\AMQPConnection $connection): \AMQPChannel
    {
        ++$this->openedChannels;

        return parent::createChannel($connection);
    }

    public function getConnectionPool(): array
    {
        return $this->connectionPool;
    }

    public function getOpenedChannelsCount(): int
    {
        return $this->openedChannels;
    }
}
