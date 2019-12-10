<?php

namespace Drupal\site_info_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Configure webservice for the site.
 */
class SiteInfoAPIController extends ControllerBase {

  /**
   * Config Factory service object.
   *
   * @var \Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * SiteInfoAPIController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory Service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Getjson values of content type 'page'.
   *
   * @param int $nid
   *   Node id.
   *
   * @return object
   *   JSON response object.
   */
  public function getJsonData($nid = NULL) {
    $jsonData = [];
    $siteApiKey = $this->config('site_info.settings')->get('siteapikey');
    $type = 'page';
    if ($siteApiKey && $nid) {
      $nodes = $this->entityTypeManager()
        ->getListBuilder('node')
        ->getStorage()
        ->loadByProperties([
          'type' => $type,
          'nid' => $nid,
        ]);

      if (!empty($nodes)) {
        foreach ($nodes as $node) {
          $jsonData = $node->toArray();
        }
      }
      // Return node as a json format.
      return new JsonResponse($jsonData);
    }
    else {
      // Return access denied page.
      $url = Url::fromRoute('system.403');
      return new RedirectResponse($url->toString());
    }
  }

}
