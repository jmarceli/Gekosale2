<?php

namespace Gekosale;

class LogoutModel extends Component\Model
{

	public function logout ()
	{
		if ($this->registry->router->getAdministrativeMode() == 1){
			App::getModel('Frontend/login/login')->destroyAdminAutologinKey();
			Session::flush();
			App::redirect('login');
		}
		else{
			Session::flush();
			App::redirect('');
		}
	}
}