<?php

declare(strict_types=1);

namespace App\Messenger\Amqp;

use Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransport;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransportFactory as SymfonyAmqpTransportFactory;
use Symfony\Component\Messenger\Transport\AmqpExt\Connection;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * This workaround fixes issues with RabbitMQ loosing connection when connection stays idle for longer than 350 sec.
 *
 * Solution is simple - use single connection with multiple channels. By default, Symfony creates new connection per
 * transport, even if the transport uses the same server. We override this behaviour by reusing previously opened
 * connection. Thanks to this, it won't be idle, as the worker will keep asking for new messages often enough to keep it alive.
 *
 * @see https://docs.aws.amazon.com/elasticloadbalancing/latest/network/network-load-balancers.html#connection-idle-timeout
 * @see https://github.com/php-enqueue/enqueue-dev/issues/1162#issuecomment-994157174
 * @see https://github.com/symfony/symfony/issues/36538
 */
class AmqpTransportFactory extends SymfonyAmqpTransportFactory
{
    private AmqpFactory $amqpFactory;

    public function __construct(AmqpFactory $amqpFactory)
    {
        $this->amqpFactory = $amqpFactory;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        unset($options['transport_name']);

        return new AmqpTransport(Connection::fromDsn($dsn, $options, $this->amqpFactory), $serializer);
    }
}
