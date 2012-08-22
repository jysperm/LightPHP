/*
该套示例中用到的数据库
直接导入MySQL即可
*/

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(255) NOT NULL,
  `passwd` text NOT NULL,
  `regtime` int(11) unsigned NOT NULL,
  `website` text NOT NULL,
  `info` text NOT NULL,
  `email` text NOT NULL,
  `avatar` varchar(16) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `user` (`uid`, `uname`, `passwd`, `regtime`, `website`, `info`, `email`, `avatar`) VALUES
(1, 'jybox', '1a91f14ce74e651beba69dc813cefb058bf3ab48', 1338480000, 'http://jyprince.me/', '王子亭', 'm@jybox.net', ''),
(2, 'whtsky', '946f4fe5095b1a0749cf2676613acb55cbd74825', 1338480000, '', 'iam whtsky', 'whats@gmail.com', ''),
(3, 'abreto', 'a42ce82da177fdc35a79ed3d0c5c8603c0c8447b', 1338480000, '', '', '', ''),
(4, 'abort', 'f4f1dfdaf4a1cf1b1f3f111fd43cf73cd8b21ab7', 1338480000, '', '', 'm@sunyboy.cn', ''),
(5, 'zeroms', '64556c314e890f5d041a22c808918b5d997ae22e', 1338480000, '', '', '', '');

CREATE TABLE IF NOT EXISTS `lp-cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
