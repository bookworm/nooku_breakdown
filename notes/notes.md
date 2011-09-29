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
events system is an observer pattern. Bingo! The miracle of understanding. The biggest difference betweencomamnd chains and events is that command chains just find the correct method to execute then run it; events execute all the callbacks.


