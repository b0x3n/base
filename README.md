# The base framework.

Busy clearing out my old github account and migrating some stuff I want
to keep to this account - base was a very barebones php framework I built
some years ago to build some projects on Upwork.

It was a rewrite of an even simpler framework I'd written called Mana.

This was the last update from mid 2020:


## Improved templating engine.

I've designed it so that the page templates can include
CSS - we can add styling using:

```
	[CSS[
		.some-style {
			...
		}
	]CSS]
```

These blocks are extracted from the templates and compiled
and inserted into the HTML document, we can do similar with
JavaScript:

```
	[JS[
		some code
	]JS]
```

Then, in our layout:

```
	<!DOCTYPE html>
	<html lang="en">
		<head>
			<style>
				<!-- collected CSS blocks are compiled and expanded here -->
				[[CSS]]
			</style>
			<script>
				<!-- collected JS blocks are compiled and expanded here -->
				[[JS]]
			</script>
		</head>
```

This works pretty well, I've also ensured that the CSS and
JS code are eval()'d in php so we can use variables and
other php goodies within these blocks - allowing for fairly
complex and dynamic code to be generated for the front-end.

This has been awesome since a template is now more like a
self-contained component, it's a nice way to work. Some
might not like that a huge chunk of CSS will be inlined 
into their document - you can still include a .css file
directly if that's what you want. But in terms of speed
it's one HTTP request less at the end of the day.


## Improved data model.

I uploaded this class a few days ago because it's useful just
on it's own. There were odd isssues with Mana and how I was
defining/creating table schemas - it was more complex than it
had to be and tightly coupled with other modules within Mana,
which was bad!


## Static hell

Yeah, I ued a lot of singletons and static classes in Mana,
seemed like a good way to go at the time but cracks did start
to show when the project got larger.

That's gone - I've also added a batching system that allows
a single request to queue up multiple actions for execution,
it's running really well and offers far more flexibility than
Mana did - so definitely a huge improvement and I'm pretty
pleased with how it's performing.

