<?php
/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2013 WellCommerce sp. z o.o.. Zabronione jest usuwanie informacji o licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version. 
 * 
 * 
 * $Revision: 438 $
 * $Author: gekosale $
 * $Date: 2011-08-27 11:29:36 +0200 (So, 27 sie 2011) $
 * $Id: tagsbox.php 438 2011-08-27 09:29:36Z gekosale $
 */

namespace Gekosale;

class MostSearchedBoxModel extends Component\Model
{

  public function getTopSearched ($view_id)
  {
		$sql = "SELECT 
					idmostsearch,
					name, 
					textcount
				FROM mostsearch
        WHERE viewid = :viewid
        ORDER BY textcount DESC
        LIMIT 1";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->execute();
		$Data = Array();
		if ($rs = $stmt->fetch()){
			$Data = Array(
				'idmostsearch' => $rs['idmostsearch'],
				'name' => $rs['name'],
				'textcount' => $rs['textcount']
			);
		}
		return $Data;
  }

	public function getAllMostSearched ()
	{
    $top_count = $this->getTopSearched(Helper::getViewId());

		$sql = "SELECT 
					idmostsearch,
					name, 
					textcount
				FROM mostsearch
				WHERE viewid = :viewid";
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->execute();
		$Data = Array();
		while ($rs = $stmt->fetch()){
			$Data[] = Array(
				'idmostsearch' => $rs['idmostsearch'],
				'name' => $rs['name'],
				'phrase' => $rs['name'],
				'textcount' => $rs['textcount'],
        // search phrase as a percentage of top searched
				'percentage' => ceil(($rs['textcount'] / $top_count['textcount']) * 100)
			);
		}
		return $Data;
	}

}
