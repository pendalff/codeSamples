<?php
/**
 * Example controller used  xslt render and simple view context object.
 * For test srbac-related - "ctrl-c-ctrl-v" change parent to \XsbaseController
 */
class DefaultController extends \XbaseController
{

  /**
   * @var stdClass $test - test values for xslt param setter
   */
  public $test = null;

  public function actionIndex()
  {
    //set to view $xslArr[head]=1
    $this->view('head', 1);
    //set to view $xslArr[lang]=ru
    $this->view('lang', 'ru');

    /**
     * @var test setters values active elements from context objects
     */
    $testStd = new stdClass();
    $testStd->action = $this->getAction()->getId();

    $testStd->form = new stdClass();
    $testStd->form->labels = new stdClass();
    $testStd->form->labels->mask = 'Label value setter from xml param (controller->form->labels->mask)';
    $this->test = $testStd;

    /**
     * @var $rowsArray - rows view data for render xslt( or for settings on active elements )
     */
    $rowsArray = array();
    //first way - bind data, dont set - after all data defined and NOT ALL fulled ( hello foreach`s!)
    $this->view->bind('body.rows', $rowsArray);

    $rowsArray[] = array(
      'id' => '1',
      'time' => '11-11',
      'data' => '12.12.12',
      'type' => 'APP'
    );
    $rowsArray[] = array(
      'id' => '2',
      'time' => '12-12',
      'data' => '12.12.12',
      'type' => 'CN'
    );

    //another way - dont bind, set value after all data defined and fulled
    //$this->view('body.rows', $rowsArray );

    $this->render('index');

  }

}