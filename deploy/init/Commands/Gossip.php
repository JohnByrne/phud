<?php
namespace Phud\Commands;
use Phud\Server,
	Phud\Actors\User as lUser;

class Gossip extends User
{
	protected $alias = 'gossip';

	public function perform(lUser $user, $args = [])
	{
		$message = implode(' ', array_slice($args, 1));
	
		foreach(Server::instance()->getClients() as $cl)
			if($cl->getUser()) {
				$u = $cl->getUser();
				Server::out($u, ($u == $user ? "You gossip" : $u." gossips").", \"".$message."\"");
			}
	}
}
?>