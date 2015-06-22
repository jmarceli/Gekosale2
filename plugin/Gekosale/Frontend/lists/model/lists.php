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
 * $Id: lists.php 438 2011-08-27 09:29:36Z gekosale $ 
 */

namespace Gekosale;

class ListsModel extends Component\Model
{
	public function getCountries($ids = array())
	{
    if(!empty($ids)) { // get only countries with ids from array
      if(!is_array($ids)) $ids = array($ids);
      $sql = 'SELECT 
            C.idcountry as countryid, 
            C.name
            FROM country C
            WHERE idcountry IN ('.implode(',', $ids).')';//:ids)';
      $stmt = Db::getInstance()->prepare($sql);
      //$stmt->bindValue('ids', $ids);//implode(',', $ids)); // 261 = Polska
    }
    else { // get all
      $sql = 'SELECT 
            C.idcountry as countryid, 
            C.name
          FROM country C';
      $stmt = Db::getInstance()->prepare($sql);
    }

    $stmt->execute();
		return $stmt->fetchAll();
	}

	public function getCountryForSelect($ids = array())
	{
    $countries = $this->getCountries($ids);
    $data = array();
    foreach($countries as $country) {
      $data[$country['countryid']] = \Gekosale\_($country['name']);
    }
    return $data;
  }
}
