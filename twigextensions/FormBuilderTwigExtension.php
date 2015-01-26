<?php  
namespace Craft;

use Twig_Extension;  
use Twig_Filter_Method;

class FormBuilderTwigExtension extends \Twig_Extension  
{
  public function getName() {
    Craft::t('AddSpace');
  }

  public function getFilters() {
    return array(
     'addSpace' => new Twig_Filter_Method($this, 'addSpace'),
     'checkArray' => new Twig_Filter_Method($this, 'checkArray'),
    );
  }

  public function addSpace($string) {
    $addSpace = preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
    $fullString = ucfirst($addSpace);
    return $fullString;
  }

  public function checkArray($array) {
    $check = is_array($array);
    return $check;
  }
}