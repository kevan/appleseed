create table `lost_ProfilePage` ( `Post_PK` int(11) not null auto_increment, `Owner_FK` int(11) default null, `Action` char(32) default null, `ActionOwner` char(128) default null, `ActionLink` char(255) default null, `Description` text default null, `Comment` text default null, `Thumb` char default null, `Stamp` datetime default null, `Current` tinyint(1) default null, primary key (`Post_PK`) ) engine=myisam default charset=utf8;