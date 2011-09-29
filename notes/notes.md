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

