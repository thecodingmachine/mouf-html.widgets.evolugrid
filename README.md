Evolugrid: a very flexible Ajax table for Mouf
==============================================

Evolugrid is an Ajax table library that can be used to display data sets in your webapps.

It comes in 2 parts:

- A jQuery plugin (evolugrid.js)
- A PHP library that can generate the JS, and that integrates well with the Mouf framework (although it can be used without Mouf)

You can use the JS library as a stand-alone, if you are not using PHP as your backend.

Why Evolugrid is different
-------------------------- 

There are a number of Ajax tables out there, and Evolugrid is certainly not the most powerful of them. BUT, it has
a number of interesting features that makes it different. Especially:

- It integrates pretty well with Mouf
- The configuration of the grid can be passed along the data

In all other Ajax grids, you usually write a model (the list of columns the grid contains), and then, via an Ajax
call, you read the data. What makes Evolugrid different is that you can pass the data AND the model in the Ajax call.

There are many cases where this can be really helpful. For instance, if you have a sparse matrix to display, with a huge
number of columns but very few columns are filled, you can display only the columns that are filled. And as you paginate
through the grid, you can add/remove columns. This is not the most common use case, but when you have to do this,
Evolugrid is almost your only option if you want to keep your sanity :)

Mouf package
------------
This package is part of Mouf (http://mouf-php.com), an effort to ensure good developing practices by providing a graphical dependency injection framework.
Using Mouf's user interface, you can create your evolugrid graphically.

Documentation
-------------

There are 2 ways to use Evolugrid:

- The [JS only way](doc/evolugrid_js.md)
- The [PHP way](doc/evolugrid_php.md)