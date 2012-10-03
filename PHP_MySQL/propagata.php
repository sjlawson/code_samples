<?php
/**
 * @package Propagata
 * @version 0.9
 * @author samueljlawson@gmail.com
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


function get_baseline_post($content) {
    global $wpdb;
    
    $startpos = strpos($content, "{baseline:");
    $endpos = strpos($content, '}', $startpos);
    $poststring = substr($content, $startpos, $endpos );
    if(!strpos($poststring, "baseline" ))
        return $content;

    $pattern1 = '/{baseline:\d+}/';
    if(!preg_match($pattern1, $content))
        return $content;
    
    $idResult = array();
    $pattern = '/\d+/';
    if(!preg_match($pattern, $poststring, $idResult)) {
        return $content;
    }
    $post_ID = $idResult[0];
    
    $strQuery = "SELECT post_content, post_title FROM wp_posts
    WHERE ID = $post_ID OR post_name LIKE '".$post_ID."-revision%'
    OR post_name LIKE '".$post_ID."-autosave%'
    AND `post_content` != '' ORDER BY post_modified DESC";

    $pagepost = $wpdb->get_row($strQuery, OBJECT);
    if(!$pagepost)
        return $content;
    //can either return only the other post, or insert it into current post
    $content2 = str_replace( $poststring , $pagepost->post_content , $content );
    
    return nl2br($pagepost->post_content);
    //return $content2;
    
}

function propagate_post($post_ID) {
    global $wpdb;
    global $current_blog;

    if($current_blog->blog_id > 1)
        return;
    
    $strQuery = "SELECT * FROM wp_posts WHERE `ID` = ".$post_ID;
    $objMainPost = $wpdb->get_row($strQuery, OBJECT);

    $strQuery = "SELECT `blog_id`, `domain`, `path` FROM wp_blogs WHERE `blog_id` != 1";
    $subBlogs = $wpdb->get_results($strQuery, OBJECT);
    foreach($subBlogs as $blog) {
        //look through pages for {baseline:x}
        $blogTable = "wp_".$blog->blog_id."_posts";
        $strQuery = "SELECT `ID` FROM $blogTable WHERE `post_content` LIKE '%{baseline:".$post_ID."}%'";
        $alreadyPosted = $wpdb->get_results($strQuery, OBJECT);
        if(is_array($alreadyPosted) && count($alreadyPosted)) {
            //do nothing
            ;
        } else {
            
            //insert post
            /*
             INSERT INTO `baseline`.`wp_2_posts`
              (`ID`, `post_author`, `post_date`, `post_date_gmt`,
              `post_content`, `post_title`, `post_excerpt`,
              `post_status`, `comment_status`, `ping_status`,
              `post_password`, `post_name`, `to_ping`, `pinged`,
              `post_modified`, `post_modified_gmt`, `post_content_filtered`,
              `post_parent`, `guid`, `menu_order`, `post_type`,
              `post_mime_type`, `comment_count`
              ) VALUES (NULL, '1', '2011-03-16 00:00:00', '2011-03-16 00:00:00',
              '{baseline:8}', 'Thing 2', '', 'publish', 'open', 'open', '',
              'thing-2', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00',
              '', '0', 'http://koorsen.com/?page_id=8', '0', 'page', '', '0');
             */
            $strQuery = "SELECT Auto_increment
                            FROM information_schema.tables
                            WHERE table_name='$blogTable'
                            AND table_schema = 'baseline'";
            $row = $wpdb->get_row($strQuery, OBJECT);
            $nextId = $row->Auto_increment;

            $strQuery = "INSERT INTO $blogTable (
            `post_author`,
            `post_date`,
            `post_date_gmt`,
            `post_content`,
            `post_title`,
            `post_status`,
            `comment_status`,
            `ping_status`,
            `post_name`,
            `guid`,
            `menu_order`,
            `post_type`
             ) VALUES (
            '1',
            NOW(),
            NOW(),
            '{baseline:". $post_ID ."}',
            '".$objMainPost->post_title ."',
            'publish',
            'open',
            'open', 
            '$objMainPost->post_name',
            'http://".$blog->domain. $blog->path ."?page_id=$nextId',
            '0',
            'page'
            )";
            $wpdb->query($strQuery);
            
        }
        
    }
    
}
function propagate_delete_post($post_ID) {
    global $wpdb;
    global $current_blog;

    if($current_blog->blog_id > 1)
        return;

    $strQuery = "SELECT * FROM wp_posts WHERE `ID` = ".$post_ID;
    $objMainPost = $wpdb->get_row($strQuery, OBJECT);

    $strQuery = "SELECT `blog_id`, `domain`, `path` FROM wp_blogs WHERE `blog_id` != 1";
    $subBlogs = $wpdb->get_results($strQuery, OBJECT);
    foreach($subBlogs as $blog) {
        //look through pages for {baseline:x}
        $blogTable = "wp_".$blog->blog_id."_posts";
        $strQuery = "DELETE FROM $blogTable WHERE `post_content` LIKE '%{baseline:".$post_ID."}%'";
        $wpdb->query($strQuery);
       
    }
}

function propagate_trash_post($post_ID) {
    global $wpdb;
    global $current_blog;

    if($current_blog->blog_id > 1)
        return;

    $strQuery = "SELECT * FROM wp_posts WHERE `ID` = ".$post_ID;
    $objMainPost = $wpdb->get_row($strQuery, OBJECT);

    $strQuery = "SELECT `blog_id`, `domain`, `path` FROM wp_blogs WHERE `blog_id` != 1";
    $subBlogs = $wpdb->get_results($strQuery, OBJECT);
    foreach($subBlogs as $blog) {
        //look through pages for {baseline:x}
        $blogTable = "wp_".$blog->blog_id."_posts";
        $strQuery = "UPDATE $blogTable SET `post_status` = 'trash' WHERE `post_content` LIKE '%{baseline:".$post_ID."}%'";
        $wpdb->query($strQuery);

    }
}

function propagate_widget($id) {
	//simple version merely copied the widget
 	global $wpdb;	
    global $current_blog;
 
    if($current_blog->blog_id > 1)
        return;
     $strQuery = "SELECT * FROM wp_options WHERE `option_id` = $id";
     $objMainWidget = $wpdb->get_row($strQuery, OBJECT);
     
$strQuery = "SELECT `blog_id`, `domain`, `path` FROM wp_blogs WHERE `blog_id` != 1";
    $subBlogs = $wpdb->get_results($strQuery, OBJECT);
    foreach($subBlogs as $blog) {
        //look through pages for {baseline:x}
        $blogTable = "wp_".$blog->blog_id."_options";
        $strQuery = "INSERT INTO $blogTable (blog_id, option_name, option_value, autoload ) 
        VALUES (
        {$blog->blog_id},
        {$objMainWidget->option_name},
        {$objMainWidget->option_value},
        {$objMainWidget->autoload}
        )
        ";
        $wpdb->query($strQuery);

    }
     
}

add_filter('the_content', 'get_baseline_post');
add_filter('content_edit_pre', 'get_baseline_post' );

add_action( 'publish_page', 'propagate_post' );
add_action( 'wp_register_sidebar_widget', 'propagate_widget');
add_action( 'delete_post', 'propagate_delete_post');
add_action( 'trash_post', 'propagate_trash_post');


