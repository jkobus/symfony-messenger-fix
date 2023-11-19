This workaround fixes issues with RabbitMQ on AWS losing connection when connection stays idle for longer than 350 sec.

Solution is simple - use single connection with multiple channels rather than multiple connections each with one channel. 
By default, Symfony creates new connection per transport, even if the transport uses the same server. We override this behaviour by reusing previously opened
connection. Thanks to this, it won't be idle, as the consumer will keep asking for new messages often enough to keep it alive.

- https://docs.aws.amazon.com/elasticloadbalancing/latest/network/network-load-balancers.html#connection-idle-timeout  
- https://github.com/php-enqueue/enqueue-dev/issues/1162#issuecomment-994157174  
- https://github.com/symfony/symfony/issues/36538  
