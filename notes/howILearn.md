I typically approach this in 5-6 sweeps. 

1. I go over the code and try to read all the documentation & give myself
a good big picture of how things work. 

2. I go over again in planned manner (based on what I learned from reading over the codebase) and make notes so the big
picture really sinks in. Understanding the big picture but remembering it requires something to make it sink in. Writing
about something is one my favorite ways of sinking concepts in. If you can explain something, you probably actually
understand it.   

3. After I've done that I start re-writing the files (again in order I've mapped from previous 2 sweeps). I like to remove
the all the doc comments and strip out any fluffy code.

4. Begin to go over re-document everything. It you cant document something, you've not grasped
something.   

5. Start testing. When convenient I like to write unit tests, but if its easier I'll just start putting code through its
paces with short examples. I always run into a few bugs caused by typos, or poor understandings.   

6. By this point i've usually thought of things that should be in the framework but aren't, improvements etc. It's time to
write them and contribute them back to the project.