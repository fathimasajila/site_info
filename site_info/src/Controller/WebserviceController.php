<?php

/**
 * @file
 * Contains \Drupal\site_info\Controller\WebserviceController.
 */
 
namespace Drupal\site_info\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Configure webservice for the site.
 */
class WebserviceController extends ControllerBase {
 
 protected $twig;

  public function __construct(TwigEnvironment $twig) {
    $this->twig = $twig;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('twig')
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
  public function siteInfoGenerateJsonValues($nid = NULL) {
    header('Content-Type: application/json');
    $siteApiKey = \Drupal::config('site_info.settings')->get('siteapikey');
    $node = Node::load($nid);
    if (!empty($node) && $node->getType() == 'page' && $siteApiKey) {
      $node_info = array(
        'title' => $this->cleanStringForJson($node->title->value),
        'body' => $this->cleanStringForJson($node->body->value),
      );
      $twigFilePath = drupal_get_path('module', 'site_info') . '/templates/site_info_json.html.twig';
      $template = $this->twig->loadTemplate($twigFilePath);
      $output = $template->render($node_info);
      print $output;
      exit;
    }
    else {
	  //Redirect to Access Denied (403) page.
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
	}
 }
 
  /**
   * To remove unwanted character from json.
   * @param
   *   string: String that needed to be clean for json.
   */
  function cleanStringForJson($string) {
    $string = stripcslashes($string);
    $string = nl2br($string);
    $string = str_replace('"','\"', $string);
    $string = preg_replace('/\s+/S', " ", $string);
    $result = trim($string);
    return $result;
  }
}
