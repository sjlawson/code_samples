/**
 * @package Propagata
 * @version 0.9
 */
/*
Plugin Name: Pagina Propagata
Plugin URI: http://fatatom.com
Description: This plugin displays content from another blog post/page in the current post/page,
            to use, insert the tag: {baseline:x} where x is the ID of the post you want to display or
            on a multi-site installation, enable on the master site and child site pages will be
            auto-generated when new pages are created on the master site. 
            Note that when the referee page is trashed, all articles containing a corresponding plugin tag
            will also be deleted.
Author: Samuel Lawson
Version: 0.9
Author URI: http://sjlawson.freeshell.org/
*/


Pagina Propagata adds functionality to Wordpress multi-site 'network' installations.
The main function is to propagate newly created pages from the 'master' site (e.g. example.com)
to 'child' sites (e.g. child1.example.com).

When a new page is created on the master site, this plugin creates pages by the same name as
the original post on every child site. However the actual content is not copied.
Instead, the content of the child pages is written as a plugin 'tag' like this: {baseline:x}
Where x is the post id on the master site. The plugin causes wordpress to load the content
from the main blog in the child page when it is displayed OR (this is very important) WHEN EDITED.

What this means, is that when the content is changed on the master site, the corresponding pages
on the child sites will reflect that change UNLESS the page has been edited on the child site.
At that point the new revision of that page becomes unique. To change it back so that it loads
from the master, all one has to do is revert to an earlier version of the child page which contains
the plugin tag "{baseline:x}"

A few further notes:
When a page is trashed or deleted from the master site, it is also trashed/deleted from ALL child
sites. When the master article is deleted, the plugin searches child blogs for a posts which contain
the plugin tag and deletes posts which refer to the master. To prevent this from happening on a
child site, all one has to do is load the child page, and save/publish. This removes the plugin tag
and replaces it with content copied from the master article.