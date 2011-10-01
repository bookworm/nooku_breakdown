# Naming Conventions

To resolve the namespace issue Nooku appends a prefix to every class name: `K` for `Koowa`. 

# The base classes

Everything in Nooku inherits from some base classes that provide features not available in normal PHP classes. Things like
mixins, command chains and object identifiers are thus shared by pretty much every class.

This is a damn powerful level of abstraction and makes things like the event system & the factory dead easy. As someone who
has built factory classes one of the major issues you face is how to identify classes & their individual instances. Having
methods built into all the classes makes this a non issue. A very powerful design pattern indeed.

It all begins in `koowa/object/handlable.php` with:

```php
interface KObjectHandlable
{
  public function getHandle();
}
```            

Now this is just an interface, you'll see allot of this convention in Nooku. Its a good convention to follow, it documents
the API a class has without actually needing the code. Sure every class could have `getHandle()` but how would you know what
classes have it? Its better to just see the implements keyword and know that the class will have that particular function.   

Now according to the function docs this is used for hashing the object. I'm betting this is used in the context of mixins.
Mixins in php are accomplished using a `__call` overload and then you maintain the objects to mixed in an array associated
by the object to be mixed in. When you call a method its looked up in the array, the object is checked to see if it has that
method and then called if it does.

{::see} This is usually referred to as decorator pattern. Check out
[here](http://giorgiosironi.blogspot.com/2010/01/practical-php-patterns-decorator.html) and
[this](http://www.jasny.net/articles/how-i-php-multiple-inheritance/). The latter has an interesting discussion. As always
searching on stackoverflow for the subject is a good way to learn {:/see}
           
In koowa/object/object.php we find the following:

```php
public function getHandle()
{
  return spl_object_hash( $this );
}
```          

Now spl_object_hash is interesting, its personally my first time encountering it. It seems to be something php is aware of
itself, which means it might have some capability for retrieving the object directly from memory. {::note} That doesn't seem
to be true. `When an object is destroyed, its hash may be reused for other objects.` That leads me to believe even if its
possible, it wouldn't be reliable anyway. Nevertheless, its a standardized & canon way of creating a hash for a php object
and thus probably optimized performance. {:/note}    


A quick search for `getHandle(` reveals a mostly mixin and command chain usage. Again some pretty neat usage of inheritance,
even though mixins don't typically need events they're built in via inheritance. This means when you actually create a class
that needs events (like say a controller) it has them available. More on the command chains when I get to re-coding those
classes.

Now the next class in the inheritance chain of Nooku is `KObject` pretty much everything we see in Nooku inherits from it at
some point. It provides some interesting things, that I'll get to later but for now lets focus on the constructor.

```php
public function __construct( KConfig $config = null) 
{
  # code stuff  
}
```   

Pretty much every classes constructor in Nooku takes a KConfig object, so its pretty damn integral. What exactly is KConfig
and how does it work? If we open up `koowa/config.php` will not it implements `IteratorAggregate, ArrayAccess, Countable`
these are all core PHP classes. PHP's OOP is pretty messed up so to treat some like an array we need to implement the
correct classes.

InteratorAggregate makes KConfig [traversable](http://www.php.net/manual/en/class.traversable.php), which means you can do a
foreach loop on the object. [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) emulates array like
methods/access, i.e `$config['']`. And [Countable](http://php.net/manual/en/class.countable.php) makes `count($config)`
work.

This makes KConfig very powerful. If we think of the way we use config objects, iteration will be very useful.  

KConfig ultimately maintains this data in the $_data array. When we try to access anything (e.g via fluent interfaces
$config->table = 'awesometable') or loop over it we are hitting methods that abstract away this fact.   


Back at KObject we move onto the the `set` and `get` methods. The allow you to set and get instance variables. These are
interesting because they don't actually provide anything very usable without corresponding overload methods. Sure, you can
use them to set protected values but thats about it alone. The power comes later when you've have a class that overloads
__set or __get.

These methods ultimately alias abstractions over arrays. You might have a array of values; calling $obj->set() will in turn
hit the overload which then saves the values to correct place (the array).       

Next up we have `mixin()` this takes a `KMixinInterface` object and creates a list of methods in the class:

```php
foreach($methods as $method) {
  $this->_mixed_methods[$method] = $object;
} 
```

Then it makes a call to `$object->setMixer($this);`. Lets take a loot at `koowa/mixin/abstract.php`. It doesn't look to be
all that interesting, its just your classic decorator pattern. What is interesting though is all the different types of
mixin objects, `commandchain`, `eventdispatcher`, `callback` etc. I'm curious to see in waht context these are typically
used lets examine them one by one. 

{::note} Interesting discovery in `__call` of `KMixinAbastract`. Apparently `Call_user_func_array is ~3 times slower than
direct method calls.`. Learn something new everyday. {:/note}  

`KMixinCommandchain` seems to really be a wrapper around `KMixinCallback` and `KCommandChain` looking at the other classes
reveals the same similar wrapping scheme. These extra classes seem to be a way of allowing one to mixin complex objects like
CommandChains and events. How this ultimately works is yet to be seen.       

Back at KObject lets look at the mixin function again. Lets do a search through the code for `mixin(` and see if it turns up
anything of interest. It appears to used internally only in two contexts behaviors and command chains. Behaviors are
essentially nothing than mixins. 

Command chains route stuff through a series of handling functions until one of them can execute the command. 

{::note} I dislike the term command chain it really should be called an observer pattern. The resulting terminology is
usually clearer in the end. To me add() + run() are so much more confusing than terms like subscribe and publish. Its a
matter of taste though. {:/note}

Whats the difference between event dispatchers and command chains? At the moment I've no fucking idea. Need to dig into this
later. My intuition says command chains are just a more simplistic version of events. Events can propagate etc. Events don't
appear to be used internally. Strange. Are controller events command chains?

A google search turned up some discussion and clarified the difference between a command chain and an observer pattern.
[here](http://www.willfitch.com/the-chain-of-command-pattern-oop-techniques-in-php.html). It now appears to me that the
events system is an observer pattern. Bingo! The miracle of understanding. The biggest difference between comamnd chains and
events is that command chains just find the correct method to execute then run it; events execute all the callbacks.


Lets move onto object/identifiable.php. This is an important interface, its used throughout Nooku, to well, identify things.
Its used extensively in the KFactory for loading, instantiating, caching objects etc. The interface only defines one method
`getIdentifier()` lets do a search through koowa for it.   

The first usage I see for it is simply as a key in associative arrays. No more firguing out what object instance should
renamed before throwing it in an array. That functionality should really be within the object, not determined inside another
object.

Example:          

```php
//Add the behaviors
$this->_behaviors[$behavior->getIdentifier()->name] = $behavior;
```

Looking in some of the getIdentifier methods we typically see `return $this->_identifier;` and the above `->name` property
implies that an identifier is usually an object. What is this object then?     

Turns out even an identifier is an object in Nooku; quite a lot of abstraction isn't it? in identifier/identifier.php we
find some neat stuff. It turns out that KIdentifier is responsible for keeping track of the path to objects file, the
application it belongs to, the type etc. There is a special __tostring method deifned that returns the typical identifier
string `[application::]type.package.[.path].name` this string is split at the dots into parts. The last part goes into
$this->name, which we see above.     


I mentioned that Identifiers are used allot in KFactory, I think its time to examine KFactory. The first thing you
should know about the factory is that its static. Everything is an class method and not an instance method. The registry
itself is stored in an static var. I'm not sure the exact reasoning for this because registries can easily be accomplished
using singletons, but hopefully all will become clear later.

The first things we see prevent instantiation:     

```php
final private function __construct(KConfig $config) 
{ 
  self::$_registry = new ArrayObject();
  self::$_chain     = new KFactoryChain();      
}
final private function __clone() { }     
```

Then comes the singleton function `instantiate()` so we actually create instances at some point. A quick search turns up the
line `KFactory::instantiate();` at the beginning of `factory/factory.php`. I think evry is static simply for the sake of
API. It would be annoying (and needlessly complicated) to get $this scope to the factory or to create a global var. Its much
better to just use static methods like `KFactory::get()` for example.            

The first thing that stands out to me is $_chain, it looks interesting. Its an instance of ` new KFactoryChain(); ` which is
a simple extension of KCommandChain. The question is how are command chains utilized in Kfactory?
  
Its first usage is in the addAdapter:

```php
public static function addAdapter(KFactoryAdapterInterface $adapter)
{
  self::$_chain->enqueue($adapter);
}      
```

Command chains call things until one of the methods responds. Adapters are abstractions, you might have multiple ones
"added" but only one should be used. All the adapters called via the command chain and each one can determine if they should
respond.  

The next interesting bit and the heart of the factory is `_instantiate()`. It actually instantiates the classes and returns
them. First thing, the command chain is hit and a class name is generated. How this happens I'm not really sure. I think we
need to dig down through what happens in a command chain.    

It all starts with `$result = self::$_chain->run($identifier, $context);` we expect a string back. In fact if we get an
object we return a KException:

```php
if(!is_object($result)) {
  throw new KFactoryException('Cannot create object from identifier : '.$identifier);
}       
``` 

So how do we get a string back? I've no idea. Lets find out. Remember everything in the chain is an adapter? So lets take a
look at `factor/adapter/abstract`. If we llokc at the execute function we that it calls the adapters instantiate method.

Lets open up `factory/adapter/component.php` and see what a typical adapter's `instantiate()` method looks like. The first
thing it does is set the return value to false. This is important because a command needs to return false to fail. {::note}
Thats not 100% accurate. `KCommandChain` has a concept called a break condition and technically it could be anything that
can be compared with `===`. The break condition is almost always false though. You can look at `command/chain.php` around
`line 115` for:
 
```php
if ( $command->execute( $name, $context ) === $this->_break_condition) {
  return $this->_break_condition;
}   
```   
{:/note}   

What the component adapter seems to is basically determine the class name for an identifier. Remember fallbacks? Well, how
do you instantiate a non existent class? You cant. You've to fallback to one that exists, this adapter determines what is
the correct fallback class and returns. Then the identifier class name is set a path calculated and the file loaded. This
means an identifier in the factory for say `admin::com.things.dispatcher` will actually hold/point to an instance of
`ComDefaultDispatcher`.

What is next for the journey into Nooku? Since the dispatcher is the place that everything goes through, lets start there.

# The dispatcher

Th dispatcher appears to be nothing more than an advanced command chain. Most everything is routed through `__call()` which
then takes those methods and routes them to commands (with callbacks). Time to start re-coding.


# Misc               

It seems like everything in `http/` deals with the request/response headers. Mapping them to something meaningful.
Request/Controller classes is where the actual http shit really happens. It seems there is concept of response classes, these
are the controllers.   

## Whats up with all those empty exception classes?

```php
class KHttpException extends KException {}
```

Curious. Maybe it just makes the code clearer so instead of throwing a general exception you knwo its an `KHttpException` ? Also
could just be abstraction in case specific exception types need new features; no changing of code necessary in the future.   

## KLoader

It operates like a very simplified version of the factory. Its how 
Koowa is loaded in the (in plugins/system/koowa.php) first place:

```php
// Require the library loader
JLoader::import('libraries.koowa.koowa', JPATH_ROOT);
JLoader::import('libraries.koowa.loader.loader', JPATH_ROOT);

 //Setup the loader
KLoader::addAdapter(new KLoaderAdapterKoowa(Koowa::getPath()));
KLoader::addAdapter(new KLoaderAdapterJoomla(JPATH_LIBRARIES));
KLoader::addAdapter(new KLoaderAdapterModule(JPATH_BASE));
KLoader::addAdapter(new KLoaderAdapterPlugin(JPATH_ROOT));
KLoader::addAdapter(new KLoaderAdapterComponent(JPATH_BASE));  
```      

## Toolbars

Toolbars are rendered in a an interesting fashion. Rather than with a call to view helper they set & rendered in the controller by
setting the buffer on JDocument.

In `administrator/components/com_default/dispatcher.php`        

```php
$toolbar = KTemplateHelper::factory('toolbar', array(
  'toolbar' => $this->getController()->getToolbar()
));

//Render the toolbar
$document->setBuffer($toolbar->toolbar(), 'modules', 'toolbar');     
$document->setBuffer($toolbar->title(), 'modules', 'title');    
```    