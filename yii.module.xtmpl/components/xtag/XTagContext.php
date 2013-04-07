<?php
/**
 * Context object
 * User: sem
 * Date: 27.03.12
 * Time: 10:09
 */
class XTagContext extends CComponent implements IXTagContext
{
  /**
   * @var IXTagElementList|XTagElementList
   */
  protected $list = null;
  /**
   * @var string name of builder
   */
  protected $builder = null;
  /**
   * @var CMap $builderContext
   */
  protected $builderContext = null;
  /**
   * @var string name of parser
   */
  protected $parser = null;
  /**
   * @var CMap $parserContext
   */
  protected $parserContext = null;

  /**
   * @var null
   */
  protected $current = null;

  /**
   * Constructor.
   * @param array $properties
   * @param IXTagElementList $list
   */
  public function __construct(array $properties, IXTagElementList $list)
  {
    $this->list = $list;
    $this->builderContext = new CMap();
    $this->parserContext = new CMap();
    foreach ($properties as $name => $value) {
      if (in_array($name, array('parserContext', 'builderContext'))) {
        $this->$name->mergeWith($value);
        continue;
      }
      $this->$name = $value;
    }
    $this->current = array();
  }

  /**
   * @return array current processed item
   */
  public function getCurrent()
  {
    return $this->current;
  }

  public function setCurrent(array $item)
  {
    $this->current = $item;
  }

  public function setBuilder($builder)
  {
    $this->builder = $builder;
  }

  /**
   * @return null|string|IXTagBuilder
   */
  public function getBuilder()
  {
    return ($this->builder instanceof Closure) ? $this->builder() : $this->builder;
  }

  public function setBuilderContext(CMap $builderContext)
  {
    $this->builderContext = $builderContext;
  }

  public function getBuilderContext()
  {
    return $this->builderContext;
  }

  /**
   * @param \IXTagElementList|\XTagElementList $list
   */
  public function setList(IXTagElementList $list)
  {
    $this->list = $list;
  }

  /**
   * @return \IXTagElementList|XTagElementList
   */
  public function getList()
  {
    return $this->list;
  }

  public function setParser($parser)
  {
    $this->parser = $parser;
  }

  public function getParser()
  {
    return ($this->parser instanceof Closure) ? $this->parser() : $this->parser;
  }

  public function setParserContext(CMap $parserContext)
  {
    $this->parserContext = $parserContext;
  }

  public function getParserContext()
  {
    return $this->parserContext;
  }

}
