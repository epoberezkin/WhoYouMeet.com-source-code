WhoYouMeet.com source code
==========================

The source code of http://WhoYouMeet.com web app - it is live and will be live so feel free
to use it any way you want (I mean the app :).

It can be a useful learning material and also feel free to re-use the code any way you like.

It is provided "as is" under [CC BY 3.0](http://creativecommons.org/licenses/by/3.0/) license
with absolutely no guaranties or liability attached :). If you want a different license for some reasons
just let me know - I just like CC licenses, but I am new here so I might be wrong.

The list of all files with their functionality is in the file ```/WYM_info.php``` and all functions are explained,
but if you need any help figuring out where in the code something is implemented or how it is working,
feel free to ask [me on StackOverflow](http://stackoverflow.com/users/1816503/esp)
(I don't think you can ask questions here, let me know if it is possible - I was surprised not to find it)
with the tag [WhoYouMeet](http://stackoverflow.com/questions/tagged/WhoYouMeet).

##Functionality/"technologies" :)##

1. CodeIgniter PHP framework (used quite extensively,
I spent lots of time to choose it and to "get it" which would never be possible without
[PHPAcademy](http://www.youtube.com/phpacademy)).

2. Facebook authentication based on Facebook PHP SDK (and PHPAcademy video :).

3. Twitter authentication. CI library is based on [TwitterOAuth](https://github.com/abraham/twitteroauth)
by [Abraham Williams](https://github.com/abraham).

4. LinkedIn authentication. CI library is based on [simple-linkedinphp](http://code.google.com/p/simple-linkedinphp/)
by [Paul Mennega](http://www.linkedin.com/in/paulmennega) - it's [on github](https://github.com/epoberezkin/twitter-codeigniter) separately.

5. Public and private social APIs to load data about people (private only for LinkedIn).

6. Loading twitter page via HTTP and processing it via regex when public API is not working
(which seems to be 50% of the time) - it is used when you add a "person you want to meet"
by his/her twitter handle.

7. Loading partial views via AJAX

##Loading partial views via AJAX##

I came up with an interesting approach of loading partial views via ajax
(it can be an old idea but I've never seen it before) to make website more responsive.
It's longer than loading just data but MUCH faster than reloading the whole page -
just try navigating the site and then do the same in IE
(if there is no history API it just reloads the page, no fallback to hashes).

Loading views via AJAX is wrong conceptually but it saved a lot of development time
as I didn't have to implement rendering views in javascript.
It made site less stable and quite a mess in the frontend, but mainly because it was not planned for
and also because sometimes it loads the whole page body and sometimes only the popup content
(which was not planned for as well - popups originally were pages) keeping url updated at the same time
(without any [backbone](http://backbonejs.org), or anything like this. Using backbone is a great idea btw).

So if you are still using PHP (which is probably not such a great idea nowadays)
and want to make a website more responsive you can consider loading partial views from server
instead of rendering them in a client - if you plan for it (or if you don't have popup views you also
load via AJAX) it will not be a mess.

##WhoYouMeet needs ideas##

WhoYouMeet needs to change somehow so I'm happy to have your suggestions by [email](mailto:team@WhoYouMeet.com)
or at [@WhoYouMeet](http://twitter.com/WhoYouMeet)

And I know the code could be much better, I'd love to know how to improve it (I know many ways myself
but definitely not everything).

Thank you!
