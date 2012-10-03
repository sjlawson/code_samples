	<creationDate>2011-05-27</creationDate>
	<author>Samuel Lawson</author>
	<authorEmail>samuel@fatatom.com</authorEmail>
	<authorUrl>http://www.fatatominternetmarketing.com</authorUrl>
	<copyright>2011 Fat Atom Internet Marketing</copyright>

The back end just needs to show a list of locations, and allow add/edit/delete. Pics could be uploaded via ftp 'manually' or could create an upload handler in the component. 

location data:
---------------------
region title (e.g. Indy North)
location name (e.g. 'Zionsville')
address
city, 
state, 
zip
phone
map link
hours - associative array ('sunday' => '12:00-6:00', etc... )
photo_main_url
photo_carousel_list - (string array)
offerpage_url
--------------------

make 'offer' link to an article

note that first day of the week is Monday, rather than Sunday (programming thing)
