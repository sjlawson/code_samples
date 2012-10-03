DROP TABLE IF EXISTS `#__location`;

CREATE TABLE `#__location` (
	`id` int(11) NOT NULL auto_increment,
	`region_title` varchar(128) NOT NULL,
	`location_name` varchar(128) NOT NULL,
	`address1` varchar(255) NOT NULL,
	`address2` varchar(255) NOT NULL,
	`city` varchar(64) NOT NULL,
	`state` varchar(45) NOT NULL,
	`zip` varchar(20) NOT NULL,
	`phone` varchar(45) NOT NULL,
	`map_link` varchar(255) NULL,
	`hours` varchar(1000) NULL,
	`photo_main_url` varchar(255) NULL,
	`photo_thumb_url` varchar(255) NULL,
	`photo_carousel_list` varchar(1000) NULL,
	`offerpage_url` varchar(255) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__location` (
`region_title` ,
`location_name` ,
`address1`,
`address2`,
`city`,
`state`,
`zip`,
`phone`,
`map_link`, 
`hours` ,
`photo_main_url`,
`photo_thumb_url`,
`photo_carousel_list`,
`offerpage_url`  )
VALUES (
'Indy North;1',
'Zionsville',
'The Boone Village',
'61 Boone Village',
'Zionsville',
'IN',
'46077',
'(317) 733-3344',
'http://maps.google.com/maps/place?cid=5970297358466648322',
'9:00-8:00,
 9:00-6:00,
 CLOSED,
 9:00-8:00,
 9:00-6:00,
 9:00-6:00,
 CLOSED',
'/images/locations/zionsville.png',
'/images/locations/zionsville.png',
NULL,
'http://www.drtavel.com/contactlens/'
), (
'Indy North;1',
'Fishers',
'7035 E. 96th Street',
NULL,
'Fishers',
'IN',
'46250',
'(317) 842-5000',
'http://maps.google.com/maps/place?cid=13896856462079031151',
'9:00-8:00,
9:00-6:00,
9:00-6:00,
9:00-8:00,
9:00-6:00,
9:00-6:00,
CLOSED',
'/images/locations/fishers.png',
'/images/locations/fishers.png',
NULL,
'http://www.drtavel.com/contactlens/'),
(
'Indy South;2',
'Greenwood',
'Greenwood Park Mall',
'1251 US Hwy 31 N.,<br> Box 12',
'Greenwood',
'IN',
'46142',
'(317) 881-6708',
'http://maps.google.com/maps/place?cid=4430316376196248196',
'9:00-9:00,
9:00-9:00,
9:00-9:00,
9:00-9:00,
9:00-9:00,
9:00-9:00,
12:00-6:00',
'/images/locations/greenwood.png',
'/images/locations/greenwood.png',
NULL,
'http://www.drtavel.com/contactlens/'
);

