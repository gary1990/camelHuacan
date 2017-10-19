DROP TABLE IF EXISTS `hi_pot_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hi_pot_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sn` varchar(255) NOT NULL COMMENT '序列号',
  `result` tinyint(1) NOT NULL COMMENT '测试结果',
  `finalresult` int(1) NOT NULL COMMENT '是否最终测试结果',
  `testerid` tinyint(1) NOT NULL COMMENT '测试员id',
  `testtime` datetime NOT NULL COMMENT '测试时间',
  `testdata` varchar(255) DEFAULT NULL,
  `teststationname` varchar(255) DEFAULT NULL,
  `instrName` varchar(255) DEFAULT NULL,
  `instrSN` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`sn`),
  CONSTRAINT `testerid` FOREIGN KEY (`id`) REFERENCES `tester` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
