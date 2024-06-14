<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use SfApp\Bundle\ProductBundle\Entity\VatRate;
use App\Entity\Setting;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SettingManager
 *
 * @package App\Manager
 */
class SettingManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    private $container;

	/**
	 * SettingManager constructor.
	 *
	 * @param \Doctrine\ORM\EntityManager                               $em
	 * @param \Symfony\Component\HttpFoundation\Session\Session         $session
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 */
    public function __construct(EntityManagerInterface $em, SessionInterface $session, ContainerInterface $container)
    {
        $this->em        = $em;
        $this->session   = $session;
        $this->container = $container;
    }

    /**
     * Get repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository(Setting::class);
    }

    /**
     * Get by key
     *
     * @param      $key
     * @param null $default
     *
     * @return string | null
     */
    public function get($key, $default = null)
    {
        $setting = $this
            ->getRepository()
            ->findOneBy(array('name' => $key));
        if ($setting) {
            return $setting->getValue();
        } else {
            return $default;
        }
    }

	/**
	 * Set
	 *
	 * @param $key
	 * @param $val
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
    public function set($key, $val)
    {
        $setting = $this
            ->getRepository()
            ->findOneBy(array('name' => $key));
        if ($setting) {
            $setting->setValue($val);
        } else {
            $setting = new Setting();
            $setting->setName($key);
            $setting->setValue($val);
        }
        $this->em->persist($setting);
        $this->em->flush();

        return $this;
    }

    /**
     * Delete
     *
	 * @param $key
	 *
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
    public function delete($key)
    {
        $setting = $this
            ->getRepository()
            ->findOneBy(array('name' => $key));
        if ($setting) {
            $this->em->remove($setting);
            $this->em->flush();
        }

    }

    /**
     * Get form data for edit
     *
     * @param Form $form
     *
     * @return Form
     */
    public function getFormData(Form $form)
    {
        $form->get(Setting::COEFF_VALORISATION)->setData($this->get(Setting::COEFF_VALORISATION));
        $form->get(Setting::ADMIN_EMAIL)->setData($this->get(Setting::ADMIN_EMAIL));

        return $this->prepareClosignDateData($form, 'display');
    }

    /**
     * Set flash message by type adn value
     *
     * @param string $action
     * @param string $message
     *
     * @return void
     */
    public function setFlash($action, $message)
    {
        $this->session->getFlashBag()->set($action, $message);
    }

	/**
	 * Save data
	 *
	 * @param \Symfony\Component\Form\Form $form
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
    public function save(Form $form)
    {

        if ($form->get(Setting::COEFF_VALORISATION)->getData()) {
            $this->set(Setting::COEFF_VALORISATION, $form->get(Setting::COEFF_VALORISATION)->getData());
        }

        if ($form->get(Setting::ADMIN_EMAIL)->getData()) {
            $this->set(Setting::ADMIN_EMAIL, $form->get(Setting::ADMIN_EMAIL)->getData());
        }

        return $this;
    }

    /**
     * Prepare data for save or display in form tpl
     *
     * @param        $form
     * @param string $action
     *
     * @return string
     */
    private function prepareClosignDateData($form, $action = '')
    {
        if ($action == 'display') {
            return $form;
        } else {
            return $form->get('closing_date_day')->getData() . '-' . $form->get('closing_date_time')->getData();
        }
    }

    /**
     * Get phone number of society
     *
     * @return string
     */
    public function getFullAddress()
    {
        return $this->get(Setting::COORDONNEE_ADRESSE_KEY)
               . ' ' . $this->get(Setting::COORDONNEE_CODEPOSTAL_KEY)
               . ' ' . $this->get(Setting::COORDONNEE_VILLE_KEY);
    }

    /**
     * Get phone number of society
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        $phone  = $this->get(Setting::COORDONNEE_TELFIXE_KEY);
        $mobile = $this->get(Setting::COORDONNEE_TELMOBILE_KEY);

        // Si le numéro téléphone n'existe pas, on va récupérer le numéro mobile
        return (!empty ($phone) ? $phone : $mobile);
    }

    /**
     * Get phone number of society
     *
     * @return string
     */
    public function getMailContact()
    {
        return $this->get(Setting::CONTACTEMAIL_KEY);
    }
    
    /**
     * Get client service of society
     *
     * @return string
     */
    public function getAdminEmail()
    {
        $adminEmail  = $this->get(Setting::ADMIN_EMAIL);

        return (!empty ($adminEmail) ? $adminEmail : Setting::DEFAULT_ADMIN_EMAIL);
    }
}