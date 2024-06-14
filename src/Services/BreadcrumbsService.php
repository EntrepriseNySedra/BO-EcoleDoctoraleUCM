<?php

namespace App\Services;

use Symfony\Component\HttpKernel\KernelInterface;
use App\Tools\BreadcrumbTools;

/**
 * Description of BreadCrumbService.php.
 *
 * @package App\Services
 * @author  Joelio
 */
class BreadcrumbsService
{

	/**
	 * @var \Symfony\Component\HttpKernel\KernelInterface
	 */
	private $kernel;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	private $container;

	/**
	 * @var array
	 */
	protected $breadcrumbs = [];

	/**
	 * constructor.
	 *
	 * @param KernelInterface $kernel
	 */
	public function __construct(KernelInterface $kernel)
	{
		$this->kernel    = $kernel;
		$this->container = $this->kernel->getContainer();
	}

	/**
	 * @param        $label
	 * @param string $href
	 *
	 * @return $this
	 */
	public function add($label, $href = '')
	{

		$breadcrumb          = new BreadcrumbTools($label, $href);
		$this->breadcrumbs[] = $breadcrumb;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getBreadcrumbs()
	{
		return $this->breadcrumbs;
	}

}
