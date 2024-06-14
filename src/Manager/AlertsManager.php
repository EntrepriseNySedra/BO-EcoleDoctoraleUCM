<?php
/**
 * Description of OfferPrices.php.
 *
 * @package App\Services
 * @author  Joelio
 */

namespace App\Manager;

use App\Entity\Alerts;
use App\Entity\Users;
use App\Repository\AlertsRepository;
use App\Repository\CitiesRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class OfferPrices
 *
 * @package App\Services
 */
class AlertsManager
{

	/**
	 * @var \App\Repository\AlertsRepository
	 */
	private $alertsRepository;

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager|\Doctrine\ORM\EntityManagerInterface
	 */
	private $em;

	/**
	 * @var \App\Repository\CitiesRepository
	 */
	private $citiesRepository;

	/**
	 * OfferPrices constructor.
	 *
	 * @param \App\Repository\AlertsRepository           $alertsRepository
	 * @param \App\Repository\CitiesRepository           $citiesRepository
	 * @param \Doctrine\Common\Persistence\ObjectManager $em
	 */
	public function __construct(
		AlertsRepository $alertsRepository,
		CitiesRepository $citiesRepository,
		ObjectManager $em
	) {
		$this->alertsRepository = $alertsRepository;
		$this->em               = $em;
		$this->citiesRepository = $citiesRepository;
	}

	/**
	 * @param \App\Entity\Users $users
	 * @param array             $data
	 */
	public function addAlerts(Users $users, array $data)
	{
		$cities = $this->citiesRepository->find($data['city']);
		$alerts = new Alerts();
		$alerts->setCities($cities);
		$alerts->setUsers($users);
		$alerts->setAreaMin($data['areaMin']);
		$alerts->setAreaMax($data['areaMax']);

		$this->em->persist($alerts);

		$this->em->flush($alerts);
	}

	/**
	 * @param \App\Entity\Users $users
	 * @param int               $id
	 *
	 * @return bool
	 */
	public function deleteAlerts(Users $users, int $id)
	{

		/** @var Alerts $alerts */
		$alerts = $this->alertsRepository->findOneBy([
			                                          'id'    => $id,
			                                          'users' => $users
		                                          ]);

		if ($alerts instanceof Alerts) {
			$this->alertsRepository->delete($alerts->getId());

			return true;
		}

		return false;
	}

	/**
	 * @param \App\Entity\Users $users
	 * @param array             $data
	 *
	 * @return array
	 */
	public function checkAlerts(Users $users, array $data)
	{
		return $this->alertsRepository->findBy([
			                                       'users'   => $users,
			                                       'cities'  => $this->citiesRepository->find($data['city']),
			                                       'areaMin' => $data['areaMin'],
			                                       'areaMax' => $data['areaMax']
		                                       ]);
	}

	/**
	 * @return \App\Repository\AlertsRepository
	 */
	public function getAlertsRepository() : \App\Repository\AlertsRepository
	{
		return $this->alertsRepository;
	}

}