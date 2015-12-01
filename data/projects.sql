
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL,
  `project` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `manager` varchar(255) NOT NULL,
  `progress` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;


ALTER TABLE `projects` ADD PRIMARY KEY (`id`);
ALTER TABLE `projects` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;

--
-- Salvarea datelor din tabel `projects`
--

INSERT INTO `projects` (`id`, `project`, `status`, `manager`, `progress`) VALUES
(2, 'A16Z', 2, 'Romayne Carlyn', '30'),
(3, 'Q300', 3, 'Jonah Benny', '30'),
(8, 'x500', 4, 'Tudor Admin', '12');

