<?php
/**
 * Build all element params
 * User: sem
 * Date: 27.03.12
 * Time: 9:02
 */
class XTagBuilderParamValue extends XTagBuilderBase
{
  /**
   * @var IXTagBuilder|XTagBuilderParamContext
   */
  private $contextBuilder = null;

  /**
   * @var IXTagBuilder|XTagBuilderParamView
   */
  private $viewBuilder = null;

  protected $defaultValue = null;


  /**
   *
   * @param IXTagProcessor $proc
   */
  public function __construct(IXTagProcessor & $proc)
  {
    parent::__construct($proc);
    $this->contextBuilder = $proc->getFactory()->builder('paramValueContext');
    $this->viewBuilder = $proc->getFactory()->builder('paramValueView');

    $this->attachBehavior('modificationable', new XTagBuilderParamValueModificationable());
    $this->enableBehaviors();
  }


  /**
   * Recursive build element parameters
   * @param  mixed $currentItem
   * @return mixed
   */
  public function build($param)
  {
    $default = (isset($param['default']) ? $param['default'] : $this->defaultValue);

    if (!isset($param['val'])) {
      return $default;
    }

    $modifed = $this->beforeBuild($param);
    if (is_array($modifed)) {
      $param = $modifed;
    }

    $type = isset($param['type']) ? $param['type'] : null;
    //default - as is
    if (empty($type)) {
      $paramVal = $param['val'];
    } //int
    elseif ($type == 'int') {
      $paramVal = (int)$param['val'];
    } //bool
    elseif ($type == 'bool') {
      $paramVal = (bool)$param['val'];
    } //string
    elseif ($type == 'string') {
      $paramVal = (string)$param['val'];
    } //lang
    elseif ($type == 'lang') {
      list($cat, $message) = explode($this->delimiter, $param['val']);
      $paramVal = Yii::t($cat, $message);
    } //map value form view data
    elseif ($type == 'view') {
      $paramVal = $this->viewBuilder->build($param);

    } //map value form context(CBaseController) data
    elseif ($type == 'context') {
      $paramVal = $this->contextBuilder->build($param);
    }

    if (isset($paramVal) && isset($param['modificator'])) {
      $param['val'] = $paramVal;
      $paramAfter = $this->afterBuild($param);

      $paramVal = isset($paramAfter['val']) ? $paramAfter['val'] : $paramVal;
    }
    return isset($paramVal) ? $paramVal : $default;
  }

  /**
   * Get param value
   * @param mixed $value
   * @param null $default
   * @return void
   */
  public function setDefaultValue($value)
  {
    $this->defaultValue = $value;
  }

}