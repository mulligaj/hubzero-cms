<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for installing default content for the DrWho component
 **/
class Migration20170614150500ComDrwho extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__drwho_characters'))
		{
			$query = "INSERT INTO `#__drwho_characters` (`id`, `name`, `created`, `created_by`, `doctor`, `friend`, `enemy`, `bio`, `state`, `species`)
					VALUES
						(1,'First Doctor','2014-02-04 09:23:12',1001,1,0,0,'',1,'timelord'),
						(2,'Second Doctor','2014-02-04 09:24:42',1001,1,0,0,'',1,'timelord'),
						(3,'Third Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(4,'Fourth Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(5,'Fifth Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(6,'Sixth Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(7,'Seventh Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(8,'Eighth Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(9,'Ninth Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(10,'Tenth Doctor','2014-09-17 13:48:42',0,1,0,0,'<p>The Tenth Doctor was a charismatic mixture of apparent opposites... He could show extraordinary kindness and sensitivity, but he himself admitted he was a man who gave no second chances. As Donna Noble pointed out to him, &quot;I think sometimes you need somebody to stop you.&quot;</p>',1,'timelord'),
						(11,'Eleventh Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(12,'Twelfth Doctor','2014-09-17 13:48:42',1001,1,0,0,'',1,'timelord'),
						(13,'Dalek Emperor','2014-09-17 13:48:42',1001,0,0,1,'<p>EXTERMINATE!</p>',1,'dalek'),
						(14,'Rose Tyler','2014-09-17 13:48:42',1001,0,1,0,'<p>Rose Tyler was born to Jackie and Pete Tyler around 1986. Her father died when hit by a car while she was still a baby.</p><p>She attended Jericho Street Junior School, where she joined the gymnastics club, and won a bronze medal in competition. (TV: Rose) She left school aged sixteen to pursue a romantic relationship with local Jimmy Stone, which ended badly and apparently led to her lack of A-Levels.</p>',1,'human'),
						(15,'Strax','2014-09-17 13:48:42',1001,0,1,0,'<p>Considering he is a member of a clone race, Commander Strax is a remarkable individual. We have seen the Doctor encounter many Sontarans and whilst every other member of this belligerent species has been guided by a single-minded passion for war, Strax is a nurse. And a good one. We have witnessed him save the life of a child in a warzone, give medical advice to a weary soldier and Strax even had himself gene-spliced so he could be fit for all nursing duties. When baby Melody appeared to need a feed he proudly declared, &quot;I can produce magnificent quantities of lactic fluid!&quot;</p>',1,'sontaran'),
						(16,'Sarah Jane Smith','2014-09-17 13:48:42',1001,0,1,0,'<p>Sarah Jane and the Doctor did not get off to the best of start. She was operating as an undercover journalist when they met, impersonating Lavinia Smith in order to infiltrate a group of scientists, hoping to get a good story and find out why so many of them were vanishing. The Doctor saw straight through her pretence but showed very little interest in the young reporter, dashing off to the middle ages in order to trace the source of the disappearances. Little did he know that Sarah had sneaked aboard the TARDIS? He unwittingly whisked her back several centuries, landing her slap bang in the middle of an adventure with a Sontaran and primitive feuding forces. But Sarah was not fazed for a moment, quickly galvanising the locals into an attack on the villainous Irongron and even preaching women&quot;s lib to the startled cooks in his castle!</p>',1,'human'),
						(17,'TARDIS','2014-09-17 13:48:42',0,0,1,0,'<p>The TARDIS can travel to any point in all of time and space and is bigger on the inside than the outside due to trans-dimensional engineering, a key Time Lord discovery. It has a library, a swimming pool, a large boot room, an enormous chamber jammed with clothes and many more nooks, crannies and secrets just waiting to be discovered?</p>',1,'???');";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__drwho_seasons'))
		{
			$query = "INSERT INTO `#__drwho_seasons` (`id`, `title`, `alias`, `premiere_date`, `finale_date`, `doctor_id`, `ordering`, `created`, `created_by`, `state`)
					VALUES
						(1,'Season 1','season1','1963-11-23 00:00:00','1964-09-12 00:00:00',1,1,'2014-09-17 13:05:21',1001,1),
						(2,'Season 2','season2','1964-10-31 00:00:00','1965-07-24 00:00:00',1,2,'2014-09-17 13:05:21',1001,1),
						(3,'Season 3','season3','1965-09-11 00:00:00','1966-07-16 00:00:00',1,3,'2014-09-17 13:05:21',1001,1),
						(4,'Season 4','season4','1966-09-10 00:00:00','1967-07-01 00:00:00',2,4,'2014-09-17 13:05:21',1001,1),
						(5,'Season 5','season5','1967-09-02 00:00:00','1968-06-01 00:00:00',2,5,'2014-09-17 13:05:21',1001,1),
						(6,'Season 6','season6','1968-08-10 00:00:00','1969-06-21 00:00:00',2,6,'2014-09-17 13:05:21',1001,1),
						(7,'Season 7','season7','1970-01-03 00:00:00','1970-06-20 00:00:00',3,7,'2014-09-17 13:05:21',1001,1),
						(8,'Season 8','season8','1971-01-02 00:00:00','1971-06-19 00:00:00',3,8,'2014-09-17 13:05:21',1001,1),
						(9,'Season 9','season9','1972-01-01 00:00:00','1972-06-24 00:00:00',3,9,'2014-09-17 13:05:21',1001,1),
						(10,'Season 10','season10','1972-12-30 00:00:00','1973-06-23 00:00:00',3,10,'2014-09-17 13:05:21',1001,1),
						(11,'Season 11','season11','1973-12-15 00:00:00','1974-06-08 00:00:00',3,11,'2014-09-17 13:05:21',1001,1),
						(12,'Season 12','season12','1974-12-18 00:00:00','1975-05-10 00:00:00',4,12,'2014-09-17 13:05:21',1001,1),
						(13,'Season 13','season13','1975-08-30 00:00:00','1976-03-06 00:00:00',4,13,'2014-09-17 13:05:21',1001,1),
						(14,'Season 14','season14','1976-09-04 00:00:00','1977-04-02 00:00:00',4,14,'2014-09-17 13:05:21',1001,1),
						(15,'Season 15','season15','1977-09-03 00:00:00','1978-03-11 00:00:00',4,15,'2014-09-17 13:05:21',1001,1),
						(16,'Season 16','season16','1978-09-02 00:00:00','1979-02-24 00:00:00',4,16,'2014-09-17 13:05:21',1001,1),
						(17,'Season 17','season17','1979-09-01 00:00:00','1980-01-12 00:00:00',4,17,'2014-09-17 13:05:21',1001,1),
						(18,'Season 18','season18','1980-08-30 00:00:00','1981-03-21 00:00:00',4,18,'0000-00-00 00:00:00',1001,1),
						(19,'Season 19','season19','1982-01-04 00:00:00','1982-03-30 00:00:00',5,19,'2014-09-17 13:05:21',1001,1),
						(20,'Season 20','season20','1983-01-04 00:00:00','1983-03-16 00:00:00',5,20,'2014-09-17 13:05:21',1001,1),
						(21,'Season 21','season21','1984-01-05 00:00:00','1984-03-30 00:00:00',5,21,'2014-09-17 13:05:21',1001,1),
						(22,'Season 22','season22','1985-01-05 00:00:00','1985-03-30 00:00:00',6,22,'2014-09-17 13:05:21',1001,1),
						(23,'Season 23','season23','1986-09-06 00:00:00','1986-12-06 00:00:00',6,23,'2014-09-17 13:05:21',1001,1),
						(24,'Season 24','season24','1987-09-07 00:00:00','1987-12-07 00:00:00',7,24,'2014-09-17 13:05:21',1001,1),
						(25,'Season 25','season25','1988-10-05 00:00:00','1989-01-04 00:00:00',7,25,'2014-09-17 13:05:21',1001,1),
						(26,'Season 26','season26','1989-10-06 00:00:00','1989-12-06 00:00:00',7,26,'2014-09-17 13:05:21',1001,1),
						(27,'Series 1','series1','2005-03-26 00:00:00','2005-06-18 00:00:00',9,27,'2014-09-17 13:05:21',1001,1),
						(28,'Series 2','series2','2006-04-15 00:00:00','2006-07-08 00:00:00',10,28,'2014-09-17 13:05:21',1001,1),
						(29,'Series 3','series3','2007-03-31 00:00:00','2007-06-30 00:00:00',10,29,'2014-09-17 13:05:21',1001,1),
						(30,'Series 4','series4','2008-04-05 00:00:00','2008-06-05 00:00:00',10,30,'2014-09-17 13:05:21',1001,1),
						(31,'Series 5','series5','2010-04-03 00:00:00','2010-06-26 00:00:00',11,31,'2014-09-17 13:05:21',1001,1),
						(32,'Series 6','series6','2011-04-23 00:00:00','2011-10-01 00:00:00',11,32,'2014-09-17 13:05:21',1001,1),
						(33,'Series 7','series7','2012-09-01 00:00:00','2013-05-18 00:00:00',11,33,'2014-09-17 13:05:21',1001,1),
						(34,'Series 8','series8','2014-08-23 00:00:00','2014-11-08 00:00:00',12,34,'2014-09-17 13:05:21',1001,1);";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__drwho_character_seasons'))
		{
			$query = "INSERT INTO `#__drwho_character_seasons` (`id`, `character_id`, `season_id`)
					VALUES
						(1,1,1),
						(2,1,2),
						(3,1,3),
						(4,14,27),
						(5,14,28),
						(6,9,27),
						(7,13,27),
						(8,13,1),
						(9,16,10),
						(10,16,11),
						(11,16,12),
						(12,16,13),
						(13,16,28),
						(14,2,4),
						(15,2,5),
						(16,2,6),
						(17,3,7),
						(18,3,8),
						(19,3,9),
						(20,3,10),
						(21,3,11),
						(22,4,12),
						(23,4,13),
						(24,4,14),
						(25,4,15),
						(26,4,16),
						(27,4,17),
						(28,4,18),
						(29,5,19),
						(30,5,20),
						(31,5,21),
						(32,6,22),
						(33,6,23),
						(34,7,24),
						(35,7,25),
						(36,7,26),
						(37,10,28),
						(38,10,29),
						(39,10,30),
						(40,11,31),
						(41,11,32),
						(42,11,33),
						(43,12,34),
						(159,17,27),
						(160,17,28),
						(161,17,29),
						(162,17,30),
						(163,17,31),
						(164,17,32),
						(165,17,33),
						(166,17,34),
						(167,17,1),
						(168,17,2),
						(169,17,3),
						(170,17,4),
						(171,17,5),
						(172,17,6),
						(173,17,7),
						(174,17,8),
						(175,17,9),
						(176,17,10),
						(177,17,11),
						(178,17,12),
						(179,17,13),
						(180,17,14),
						(181,17,15),
						(182,17,16),
						(183,17,17),
						(184,17,18),
						(185,17,19),
						(186,17,20),
						(187,17,21),
						(188,17,22),
						(189,17,23),
						(190,17,24),
						(191,17,25),
						(192,17,26);";

			$this->db->setQuery($query);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__drwho_character_seasons'))
		{
			$query = "DELETE FROM `#__drwho_character_seasons`";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__drwho_characters'))
		{
			$query = "DELETE FROM `#__drwho_characters`";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__drwho_seasons'))
		{
			$query = "DELETE FROM `#__drwho_seasons`";
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}
