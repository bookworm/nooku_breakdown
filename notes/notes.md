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
Mixins in php are accomplished using a `__call` overload and then you maintain the objects to mixed in an array associated by
the object to be mixed in. When you call a method its looked up in the array, the object is checked to see if it has that
method and then called if it does. 

{::see} This is usually referred to as decorator pattern. Check out [here](http://giorgiosironi.blogspot.com/2010/01/practical-php-patterns-decorator.html) and [this](http://www.jasny.net/articles/how-i-php-multiple-inheritance/). The latter has an interesting discussion. As always searching on stackoverflow for the subject is a good way to learn 
{:/see}
           
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
these are all core PHP classes. PHP's OOP is pretty messed up so to treat some like an array we need to implement the correct
classes. 

InteratorAggregate makes KConfig [traversable](http://www.php.net/manual/en/class.traversable.php), which means you can do a
foreach loop on the object. [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) emulates array like methods/access,
i.e `$config['']`. And [Countable](http://php.net/manual/en/class.countable.php) makes `count($config)` work.

This makes KConfig very powerful. If we think of the way we use config objects, iteration will be very useful.  

KConfig ultimately maintains this data in the $_data array. When we try to access anything (e.g via fluent interfaces
$config->table = 'awesometable') or loop over it we are hitting methods that abstract away this fact.   


Back at KObject we move onto the the `set` and `get` methods. The allow you to set and get instance variables. These are
interesting because they don't actually provide anything very usable without corresponding overload methods. Sure, you can
use them to set protected values but thats about it alone. The power comes later when you've have a class that overloads
__set or __get.

These methods ultimately alias abstractions over arrays. You might have a array of values; calling $obj->set() will in turn
hit the overload which then saves the values to correct place (the array).       

If we move 

