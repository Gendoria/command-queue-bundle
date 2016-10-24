# Command Queue Bundle

[![Build Status](https://img.shields.io/travis/Gendoria/command-queue-bundle/master.svg)](https://travis-ci.org/Gendoria/command-queue-bundle)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/Gendoria/command-queue-bundle.svg)](https://scrutinizer-ci.com/g/Gendoria/command-queue-bundle/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Gendoria/command-queue-bundle.svg)](https://scrutinizer-ci.com/g/Gendoria/command-queue-bundle/?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/gendoria/command-queue-bundle.svg)](https://packagist.org/packages/gendoria/command-queue-bundle)
[![Latest Stable Version](https://img.shields.io/packagist/v/gendoria/command-queue-bundle.svg)](https://packagist.org/packages/gendoria/command-queue-bundle)

Bundle implementing command queue mechanism, making it possible to send command from main Symfony process
and execute them using pools of backend workers using [`gendoria/command-queue` library](https://github.com/Gendoria/command-queue).

Bundle created in cooperation with [Isobar Poland](http://www.isobar.com/pl/).

![Isobar Poland](doc/images/isobar.jpg "Isobar Poland logo") 

## Installation

### Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require gendoria/command-queue-bundle "~0.2.0"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle


Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Gendoria\CommandQueueBundle\GendoriaCommandQueueBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Add bundle configuration

To be able to use the bundle, you have to add correct configuration in your `app/config/config.yml`.

The example full bundle configuration looks as follows:

```yaml
gendoria_command_queue:
    enabled: true
    listeners:
        clear_logs: true
        clear_entity_managers: true
    pools:
        default:
            send_driver: '@gendoria_command_queue_rabbit_mq_driver.driver.default'
        pool2:
            send_driver: '@gendoria_command_queue.driver.direct'
    routes:
        '\Namespaced\CommandClassOrInterface': pool2
```

`enabled` property allows to enable or disable bundle (eg. on some environments like testing).

`listeners` section is optional. By default all listeners are turned on.

Pools section describes available queue pools. It is required, when bundle is enabled. At least one pool, 
named `default` has to be present.

Pool configuration has only one parameter, `send_driver`. It describes, which method will be used 
to transport commands from sender to workers. Right now, the bundle does not provide
any transportation method by itself (except of direct processing driver).
There is, though, [gendoria/command-queue-rabbitmq-bundle](https://github.com/Gendoria/command-queue-rabbitmq-bundle)
with transport implementation using RabbitMQ and `php-amqplib/rabbitmq-bundle` library.

Next section is optional `routes`. It allows defining command routes, sending specific command classes to different pools.
Routes is a hash map. The key is command class expression and the value - target pool name. Every target pool
has to be defined in `pools` section. More informations about command class expressions can be found in
[routing](#routing) section.

Routing is completely optional. If not defined, all commands will be send to default pool. 
Also, for every command class with no routing defined, default pool will be used.

## Usage

This bundle makes it possible to create a distributed execution environment for varius commands. 
Developer can use it for example to delegate processor - heavy tasks for backend execution
instead of processing them in frontend (thus impairing user experience).

### Command classes

Command class is where everything starts. It is used to pass any data required to process a command
to a worker pool.

Command class has to implement `Gendoria\CommandQueue\Command\CommandInterface`. The interface itself
does not contain any methods. It is used to 'tag' a class as a command.

Command class can contain any serializable values. They can be simple types, like integers or strings,
but they can also be objects or arrays. They should **not** be resources, like database connections
or file handles, as these are not serializable.

The example command class can look like below. This specific command uses JMS serializer, but you can use
other serialization method, if you want. Command queue bundle provides implementation for JMS and Symfony
serialization components.

```php
namespace Example\Namespace;

use Gendoria\CommandQueue\Command\CommandInterface;
use JMS\Serializer\Annotation\Type;

class ParseUrlCommand implements CommandInterface
{
    /**
     * Page URL.
     *
     * @var string
     * @Type("string")
     */
    public $url;
    
    /**
     * User ID.
     *
     * @var integer
     * @Type("integer")
     */
    public $userId;
    
    /**
     * Class constructor.
     *
     * @param string  $url    Page URL.
     * @param integer $userId User ID.
     */
    public function __construct($url, $userId)
    {
        $this->url = $url;
        $this->userId = $userId;
    }
}

```

As you can see, it has two public properties, URL and user ID. It is a simple value object without a logic
on its own. This is a suggested way, but you can include some logic inside of it.

### Command serialization

Command will be sent through some kind of queue, potentially to a completely different machine. 
As most transports cannot pass raw php object, commands have to be serialized to string representations.

This is done by serializer classes. In most cases you will not use them directly. They are injected to send
drivers and executed on your command class. You have to provide a configuration for them, though.

Command above used JMS serializer. This is the preferred method, but bundle provides you with two other options.

Each serializer implements the `Gendoria\CommandQueue\Serializer\SerializerInterface`.

#### JMS Serializer

This serializer is available as a service `gendoria_command_queue.serializer.jms`.
It uses `jms/serializer-bundle` component to create a serialized command representation.
All usage instruction can be found on [JMSSerializerBundle documentation](http://jmsyst.com/bundles/JMSSerializerBundle).

#### Symfony serializer

This serializer is available as a service `gendoria_command_queue.serializer.symfony`.
It uses Symfony serialization component.

This driver is in development phase. No detailed configuration instructions are present at the moment.
You are welcome to submit one.

> :warning: **Warning!** This serializer may not be available, if you use `jms/serializer-bundle` and it registeres
> its own service as `serializer`. There is a `enable_short_alias` key in JMS serializer configuration, that
> can change this behaviour.

### Null serializer

This serializer is available as a service `gendoria_command_queue.serializer.null`.

In some cases you don't have to serialize a command. For this, null serializer can be used. It exists, though,
mainly for testing purposes and has little real world usage.

### Sending commands

Commands are sent using queue manager service. It is responsible of routing the command to the appropriate pool
based on its configuration.

Bundle provides you with simple queue managers for each pool you define and one multiple queue manager,
which uses routing to decide, onto which pool command will be sent.

Main queue manager can be accessed as `gendoria_command_queue.manager` service. Simple queue managers are 
accessible as services `gendoria_command_queue.manager.poolName`, where `poolName` is your pool name defined
in pools configuration.

If you want to use queue manager inside your controller, you can call it like below. 
We use same ParseUrlCommand, as in command example above.

```php
namespace ExampleBundle\Controller;

use Gendoria\CommandQueue\QueueManager\MultipleQueueManagerInterface;
use Example\Namespace\ParseUrlCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/")
 */
class ExampleController extends Controller
{

    /**
     * @Route("")
     */
    public function index()
    {
            $command = new ParseUrlCommand("http://example.com", 1);

            /* @var $service MultipleQueueManagerInterface */
            $service = $this->get('gendoria_command_queue.manager');
            $service->sendCommand($command);
    }
}
```

### Command processors

The command has been created and sent. Now you have to process it somehow. Luckily, this is quite simple.

First, you have to create a command processor service. This is a class implementing
``.

```php
namespace ExampleBundle\CommandProcessor;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Example\Namespace\ParseUrlCommand;

class ParseUrlProcessor implements CommandProcessorInterface
{
    /**
     * @param ParseUrlCommand $command
     */
    public function process(CommandInterface $command)
    {
        //Parse your URL here
    }
    
    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof ParseUrlCommand;
    }
}

```

This class has to have two function. First - `supports` - returns true, if command processor can process
given command. In most cases you will just check, if the command is an instance of a given class.

Second function - process - is where command processing actually takes place.

When you have your service class, you have to register it as a service and tag with command processor tag.
For the processor above, configuration can look like following.

```yaml
services:
    example_bundle.processor.parse_url:
        class: ExampleBundle\CommandProcessor\ParseUrlProcessor
        tags:
            - name: gendoria_command_queue.processor
              command: Example\Namespace\ParseUrlCommand

```

The service cannot be abstract or private, as services are lazy loaded, when the command comes from queue.

That's it!

### <a name="routing"></a>Routing

By default, bundle passes all commands to default queue (and default worker pool). If you want some commands
to be executed by a specific set of workers, you can use routing. One posible use case could be,
that you want to send e-mails to users from a specific server, not your default backend one.
Or you have some commands so heavy in processing (like video reencoding), that they would have clogged
your default queue. Then you can use routing to send these commands to a specialized pool of workers.

When using multiple queue manager provided by the bundle, you can define your routes 
inside your configuration file. One route entry is a hashmap, where key is route expression 
and the value - target pool name.

When matching routes, manager uses name of a command class and its ancestors, as well, as interfaces 
implemented by the command and its ancestors. Several rues apply here:

- Class names are **always** more important than interface names
- Child class name is more important, than the ancestors class names
- When comparying interfaces, interfaces implemented by the child class are more important, than these implemented
  by the base class.
- If nothing is detected, `default` pool is used

So, if you have a class `X` implementing interface `Y` and routes for both `X` and `Y`, route for `X` will be
used.

If you have class with inheritance `A -> B -> C`, where `A` is most base class and `C` - child class,
routing for `C` will be used. If no routing for `C` exists, manager considers routing for `B` and then `A`.
If nothing is detected, `default` pool will be used.

Additionally, you can use `*` in your route expression to indicate any string value. 
So, for example, if your expression is `*Something` it will match class `ASomething` and `BSomething`, but not
`SomethingC`. Similary, expression `Example\Namespace\*` will match all commands in example namespace.

Wildcard routes are less important, than simple routes on single class / interface comparision. When inheritance
and / or interfaces come onto play, it becomes more complex and harder to tell. We advise to use routing 
on most child classes, using wildcards, and not to create routing for base classes / interfaces, 
unless absolutely necessary.

### Send drivers

Bundle can use arbitrary transportation mechanisms to send commands to command processors. 
These mechanisms are called 'send drivers'.

By default, bundle provides only direct processing driver service. It executes command right after it is send,
on same process as sender. It does not serialize command prior sending, so no serialization configuration 
is needed.

Other send drivers (eg. one using RabbitMQ queue mechanism) are provided by separate bundles.

### Queue managers

Documentation in progress
