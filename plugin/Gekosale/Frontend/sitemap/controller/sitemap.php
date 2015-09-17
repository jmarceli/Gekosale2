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
 * $Id: sitemap.php 438 2011-08-27 09:29:36Z gekosale $
 */

namespace Gekosale;

class SitemapController extends Component\Controller\Frontend
{

	public function index ()
	{
		if ($this->registry->core->getParam() > 0){
      $this->sitemap();
    }
		else{
			$this->Render('Sitemap');
		}
	}

  public function sitemap ()
  {
    try{
      // one sitemap for all search engines (use Google sitemap settings)
      App::getModel('sitemap')->generateSitemap(2);
    }
    catch (Exception $e){
      echo $e->getMessage();
    }
  }

}
