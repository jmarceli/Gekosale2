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
 * $Id: db.class.php 438 2011-08-27 09:29:36Z gekosale $ 
 */

namespace Gekosale;

class Translation
{
	
	private static $translations = Array();

	public static function get ($name)
	{
		if (empty(self::$translations)){
			self::loadTranslations();
		}
		if (! is_null($name) && ($name != '') && (isset(self::$translations[$name]))){
			return self::$translations[$name];
		}
		else{
			return $name;
		}
	}

	public static function loadTranslations ()
	{
		if ((self::$translations = App::getRegistry()->cache->load('translations')) === FALSE){
			$sql = 'SELECT 
						T.name,
						TD.translation 
					FROM translation T
					LEFT JOIN translationdata TD ON T.idtranslation = TD.translationid
					WHERE TD.languageid = :languageid';
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->bindValue('languageid', Helper::getLanguageId());
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				self::$translations[$rs['name']] = htmlspecialchars($rs['translation']);
			}
			App::getRegistry()->cache->save('translations', self::$translations);
		}
	}

}

/**
 * Return translated version of $name
 * @param string $name 
 */
function _($name) {
    return Translation::get($name);
}
